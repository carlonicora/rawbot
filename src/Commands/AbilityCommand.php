<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\AbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharacterAbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharacterAbilitiesDataWriter;
use CarloNicora\Minimalism\Raw\Data\Objects\CharacterAbility;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Services\Discord\Enums\DiscordCommandOptionType;
use Exception;
use RuntimeException;

class AbilityCommand extends AbstractCommand
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
                hasBeenUsed: false,
                hasBeenUpdated: false,
            );
        }

        $characterAbility->setValue($this->request->getPayload()?->getParameter(PayloadParameter::Value));

        /** @var CharacterAbilitiesDataWriter $writeCharacterAbility */
        $writeCharacterAbility = MinimalismObjectsFactory::create(CharacterAbilitiesDataWriter::class);
        $writeCharacterAbility->update([$characterAbility]);

        $characterCommand = new CharacterCommand($this->request);
        $this->response->addResource(
            $characterCommand->getCharacterResource(
                updated: true,
            )
        );

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
            'type' => DiscordCommandOptionType::SUB_COMMAND->value,
            'name' => RawCommand::Ability->value,
            'description' => 'Set a value of one of your abilities',
            'options' => [
                [
                    'type' => DiscordCommandOptionType::INTEGER->value,
                    'name' => PayloadParameter::Value->value,
                    'description' => 'The new value of the ability',
                    'required' => true,
                ],[
                    'type' => DiscordCommandOptionType::INTEGER->value,
                    'name' => PayloadParameter::Ability->value,
                    'description' => 'The ability to set',
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
                ],
            ],
        ];
    }
}