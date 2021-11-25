<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharacterAbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharacterAbilitiesDataWriter;
use CarloNicora\Minimalism\Raw\Data\Objects\CharacterAbility;
use CarloNicora\Minimalism\Raw\Enums\CriticalRoll;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Enums\RawDocument;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Factories\CommandOptionsFactory;
use CarloNicora\Minimalism\Raw\Helpers\DiceRoller;
use CarloNicora\Minimalism\Raw\Services\Discord\ApplicationCommands\ApplicationCommand;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ApplicationCommandInterface;
use Exception;
use RuntimeException;

class RollCommand extends AbstractCommand
{
    /**
     * @return Document
     * @throws Exception
     */
    public function execute(
    ): Document
    {
        if ($this->request->getServer() === null){
            throw new RuntimeException(RawError::CampaignNotInitialised->getMessage());
        }

        if ($this->request->getPayload()?->hasParameter(PayloadParameter::Dice)) {
            $this->rollDice();
            return $this->response;
        }

        if (!$this->request->getServer()->isInSession()){
            throw new RuntimeException(RawError::NotInSession->getMessage());
        }

        if ($this->request->getCharacter() === null){
            throw new RuntimeException(RawError::CharacterNotSpecified->getMessage());
        }

        if (!$this->request->isGM() && $this->request->getCharacter()->isNPC()){
            throw new RuntimeException('Only the GM can control the NPCs!');
        }

        /** @var CharacterAbilitiesDataReader $readCharacterAbility */
        $readCharacterAbility = MinimalismObjectsFactory::create(CharacterAbilitiesDataReader::class);
        try {
            $characterAbility = $readCharacterAbility->byCharacterIdAbilityIdSpecialisation(
                characterId: $this->request->getCharacter()->getId(),
                abilityId: $this->request->getPayload()?->getParameter(PayloadParameter::Ability),
                specialisation: ($this->request->getPayload()?->hasParameter(PayloadParameter::Specialisation) ? $this->request->getPayload()?->getParameter(PayloadParameter::Specialisation) : '/'),
            );
        } catch (Exception) {
            $characterAbility = new CharacterAbility(
                characterId: $this->request->getCharacter()->getId(),
                abilityId: $this->request->getPayload()?->getParameter(PayloadParameter::Ability),
                specialisation: ($this->request->getPayload()?->hasParameter(PayloadParameter::Specialisation) ? $this->request->getPayload()?->getParameter(PayloadParameter::Specialisation) : '/'),
                value: 0,
                hasBeenUpdated: false,
            );
        }

        $characterAbility->markAsUsed();

        /** @var CharacterAbilitiesDataWriter $writeCharacterAbilities */
        $writeCharacterAbilities = MinimalismObjectsFactory::create(CharacterAbilitiesDataWriter::class);
        $writeCharacterAbilities->update([$characterAbility]);

        $bonus = $this->request->getPayload()?->getParameter(PayloadParameter::Bonus);
        $critical = CriticalRoll::None;
        $roll = DiceRoller::roll(20, $critical);
        $total = $characterAbility->getValue() + $this->request->getCharacter()->getTraitValue($characterAbility->getAbility()->getTrait()) + $roll;

        if ($bonus !== null) {
            $total += (int)$bonus;
        }

        if ($characterAbility->getValue() === 0) {
            $total -= 10;
        }

        if ($critical === CriticalRoll::Success){
            $total += 20;
        } elseif ($critical === CriticalRoll::Failure){
            $total -= 21;
        }

        if ($total < 0){
            $successes = 0;
        } else {
            $successes = (int)($total / 25);
        }

        $rollObject = new ResourceObject(
            type: RawDocument::Roll->value,
        );
        $rollObject->relationship('character')->resourceLinkage->add($this->request->getCharacter()->generateResourceObject());
        $rollObject->relationship('characterAbility')->resourceLinkage->add($characterAbility->generateResourceObject());
        $rollObject->relationship('ability')->resourceLinkage->add($characterAbility->getAbility()->generateResourceObject());
        $rollObject->attributes->add('roll', $roll);
        $rollObject->attributes->add('critical', $critical->value);
        $rollObject->attributes->add('total', $total);
        $rollObject->attributes->add('successes', $successes);
        if ($bonus !== null) {
            $rollObject->attributes->add('bonus', $bonus);
        }

        $this->response->addResource($rollObject);

        return $this->response;
    }

    /**
     * @throws Exception
     */
    private function rollDice(
    ): void
    {
        $dice = $this->request->getPayload()?->getParameter(PayloadParameter::Dice);
        $bonus = $this->request->getPayload()?->getParameter(PayloadParameter::Bonus);

        try {
            [$quantity, $sides] = explode('d', $dice);

            if (!is_int((int)$quantity) || !is_int($sides)){
                throw new RuntimeException('Wrong dice - 1');
            }
        } catch (Exception) {
            throw new RuntimeException('Wrong dice - 2');
        }

        $total = 0;

        for ($round = 1; $round <= $quantity; $round++){
            $result = DiceRoller::simpleRoll($sides);

            $total += $result;

            $diceResource = new ResourceObject(
                type: RawDocument::Dice->value,
                id: $round,
            );
            $diceResource->attributes->add('type', 'd' . $sides);
            $diceResource->attributes->add('roll', $result);
            $this->response->addResource($diceResource);
        }

        if ($bonus !== null){
            $this->response->meta->add('bonus', $bonus);
            $total += (int)$bonus;
        }

        $this->response->meta->add('result', $total);
        $this->response->meta->add('dice', $dice);
    }

    /**
     * @param int|null $serverId
     * @return ApplicationCommandInterface
     * @throws Exception
     */
    public function getDefinition(
        ?int $serverId=null,
    ): ApplicationCommandInterface
    {
        $response = new ApplicationCommand(
            id: RawCommand::Roll->value,
            applicationId: '??',
            name: RawCommand::Roll->value,
            description: 'Roll an ability or a dice',
        );

        $response->addOption(CommandOptionsFactory::getRollDiceCommand());
        $response->addOption(CommandOptionsFactory::getRollAbilityCommand());

        return $response;
    }
}