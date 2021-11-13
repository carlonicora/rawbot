<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharacterAbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharacterAbilitiesDataWriter;
use CarloNicora\Minimalism\Raw\Data\Objects\CharacterAbility;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Factories\CommandOptionsFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\ApplicationCommands\ApplicationCommand;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ApplicationCommandInterface;
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

        $characterCommand = new CharacterCommand(
            request: $this->request,
            raw: $this->raw,
        );
        $this->response->addResource(
            $characterCommand->getCharacterResource(
                updated: true,
            )
        );

        return $this->response;
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
            id: RawCommand::Ability->value,
            applicationId: '??',
            name: RawCommand::Ability->value,
            description: 'Set a value of one of your abilities',
        );

        $response->addOption(CommandOptionsFactory::getAbilityListSubOption());
        $response->addOption(CommandOptionsFactory::getValueSubOption('The new value of the ability'));
        $response->addOption(CommandOptionsFactory::getAbilitySpecialisationSubOption());
        $response->addOption(CommandOptionsFactory::getCharacterSelectionSubOption());

        return $response;
    }
}