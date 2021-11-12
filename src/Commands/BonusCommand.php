<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharacterAbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharacterAbilitiesDataWriter;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharactersDataWriter;
use CarloNicora\Minimalism\Raw\Data\Objects\CharacterAbility;
use CarloNicora\Minimalism\Raw\Enums\CriticalRoll;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Factories\CommandOptionsFactory;
use CarloNicora\Minimalism\Raw\Helpers\DiceRoller;
use CarloNicora\Minimalism\Raw\Services\Discord\ApplicationCommands\ApplicationCommand;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ApplicationCommandInterface;
use Exception;
use RuntimeException;

class BonusCommand extends AbstractCommand
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

        if ($this->request->getCharacter() === null){
            throw new RuntimeException(RawError::CharacterNotSpecified->getMessage());
        }

        if ($this->request->getPayload()?->hasParameter(PayloadParameter::Up)){
            $this->up();
        } elseif ($this->request->getPayload()?->hasParameter(PayloadParameter::Roll)){
            $this->roll();
        } else {
            if (!$this->request->isGM()){
                throw new RuntimeException('Only the GM can do this');
            }
            $this->assign();
        }

        return $this->response;
    }

    /**
     * @throws Exception
     */
    private function assign(
    ): void
    {
        $value = $this->request->getPayload()?->getParameter(PayloadParameter::Value);
        if ($value === null)  {
            $value = 0;
        }

        $this->addBonus($value);

        $bonusResource = new ResourceObject(
            type: 'bonus'
        );
        $bonusResource->attributes->add('type', 'assign');
        $bonusResource->attributes->add('value', $value);
        $bonusResource->relationship('character')->resourceLinkage->add($this->request->getCharacter()?->generateResourceObject());

        $this->response->addResource($bonusResource);
    }

    /**
     * @param int $bonus
     * @throws Exception
     */
    private function addBonus(
        int $bonus,
    ): void
    {
        $this->request->getCharacter()?->addBonus($bonus);

        /** @var CharactersDataWriter $writeCharacter */
        $writeCharacter = MinimalismObjectsFactory::create(CharactersDataWriter::class);
        $writeCharacter->update([$this->request->getCharacter()]);
    }

    /**
     * @return CharacterAbility
     * @throws Exception
     */
    public function getCharacterAbility(
    ): CharacterAbility
    {
        /** @var CharacterAbilitiesDataReader $readCharacterAbility */
        $readCharacterAbility = MinimalismObjectsFactory::create(CharacterAbilitiesDataReader::class);

        try {
            $response = $readCharacterAbility->byCharacterIdAbilityIdSpecialisation(
                characterId: $this->request->getCharacter()?->getId(),
                abilityId: $this->request->getPayload()?->getParameter(PayloadParameter::Ability),
                specialisation: $this->request->getPayload()?->getParameter(PayloadParameter::Specialisation)??'/',
            );

            if ($response->getValue() === 0) {
                throw new RuntimeException('');
            }
        } catch (Exception) {
            throw new RuntimeException('You cannot update an ability your character is untrained in');
        }

        return $response;
    }

    /**
     * @param CharacterAbility $characterAbility
     * @throws Exception
     */
    public function updateCharacterAbility(
        CharacterAbility $characterAbility,
    ): void
    {
        /** @var CharacterAbilitiesDataWriter $writeCharacterAbility */
        $writeCharacterAbility = MinimalismObjectsFactory::create(CharacterAbilitiesDataWriter::class);

        $writeCharacterAbility->update([$characterAbility]);
    }

    /**
     * @param CharacterAbility $characterAbility
     * @param string $type
     * @return ResourceObject
     * @throws Exception
     */
    private function getBonusResource(
        CharacterAbility $characterAbility,
        string $type,
    ): ResourceObject
    {
        $response = new ResourceObject(
            type: 'bonus'
        );
        $response->attributes->add('type', $type);
        $response->relationship('character')->resourceLinkage->add($this->request->getCharacter()?->generateResourceObject());
        $response->relationship('characterAbility')->resourceLinkage->add($characterAbility->generateResourceObject());
        $response->relationship('ability')->resourceLinkage->add($characterAbility->getAbility()->generateResourceObject());

        return $response;
    }

    /**
     * @throws Exception
     */
    private function up(
    ): void
    {
        $characterAbility = $this->getCharacterAbility();

        if (!$characterAbility->hasBeenUpdated()){
            throw new RuntimeException('The ability you have tried to update has not been increased during the last session.');
        }

        $this->addBonus(-1);
        $characterAbility->addValue(1);

        $bonusResource = $this->getBonusResource($characterAbility, 'up');
        $this->response->addResource($bonusResource);

        $this->updateCharacterAbility($characterAbility);
    }

    /**
     * @throws Exception
     */
    private function roll(
    ): void
    {
        $characterAbility = $this->getCharacterAbility();
        $this->addBonus(-1);

        $critical = CriticalRoll::None;
        $roll = DiceRoller::roll(100, $critical);

        $delta = $roll - ($characterAbility->getValue() + $this->request->getCharacter()?->getTraitValue($characterAbility->getAbility()->getTrait()));

        if ($delta > 0){
            $bonus = (int)($delta/20)+1;
            if ($critical === CriticalRoll::Success){
                $bonus *= 2;
            }

            $characterAbility->addValue($bonus);
            $this->updateCharacterAbility($characterAbility);
        } else {
            $bonus = 0;
        }

        $bonusResource = $this->getBonusResource($characterAbility, 'roll');
        $bonusResource->attributes->add('roll', $roll);
        $bonusResource->attributes->add('critical', $critical->value);
        $bonusResource->attributes->add('bonus', $bonus);
        $this->response->addResource($bonusResource);
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
            id: RawCommand::Bonus->value,
            applicationId: '??',
            name: RawCommand::Bonus->value,
            description: 'Manages the bonus in RAW',
        );

        $response->addOption(CommandOptionsFactory::getBonusRollOption());
        $response->addOption(CommandOptionsFactory::getBonusUpOtion());
        $response->addOption(CommandOptionsFactory::getBonusAssignCommand());

        return $response;
    }
}