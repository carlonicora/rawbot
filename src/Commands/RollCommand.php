<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\AbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharacterAbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharacterAbilitiesDataWriter;
use CarloNicora\Minimalism\Raw\Data\Objects\CharacterAbility;
use CarloNicora\Minimalism\Raw\Enums\CriticalRoll;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Helpers\DiceRoller;
use CarloNicora\Minimalism\Raw\Services\Discord\Enums\DiscordCommandOptionType;
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
            throw new RuntimeException(RawError::CampaignAlreadyInitialised->getMessage());
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

        if ($total < 0){
            $successes = 0;
        } else {
            $successes = (int)($total / 25);
        }

        $rollObject = new ResourceObject(
            type: 'roll',
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
     * @param int|null $serverId
     * @return array
     * @throws Exception
     */
    public function getDefinition(
        ?int $serverId=null,
    ): array
    {
        /** @var AbilitiesDataReader $readAbility */
        $readAbility = MinimalismObjectsFactory::create(AbilitiesDataReader::class);
        $abilities = $readAbility->all();

        $abilitiesList = [];
        foreach ($abilities ?? [] as $ability){
            $abilitiesList[] = [
                'name' => $ability->getFullName() . ' (' . $ability->getTrait()->value . ')',
                'value' => $ability->getId(),
            ];
        }

        return [
            'name' => RawCommand::Roll->value,
            'description' => 'roll an ability or a dice',
            'options' => [
                [
                    'type' => DiscordCommandOptionType::INTEGER->value,
                    'name' => PayloadParameter::Ability->value,
                    'description' => 'The name of the ability you want to use.',
                    'required' => true,
                    'choices' => $abilitiesList,
                ],[
                    'type' => DiscordCommandOptionType::STRING->value,
                    'name' => PayloadParameter::Character->value,
                    'description' => '[GM Only] Select the npc identifier',
                    'required' => false,
                ],[
                    'type' => DiscordCommandOptionType::STRING->value,
                    'name' => PayloadParameter::Specialisation->value,
                    'description' => 'The ability specialisation (if any)',
                    'required' => false,
                ],[
                    'type' => DiscordCommandOptionType::STRING->value,
                    'name' => PayloadParameter::Bonus->value,
                    'description' => 'Advantage or disadvantage to the roll',
                    'required' => false,
                ],
            ],
        ];
    }
}