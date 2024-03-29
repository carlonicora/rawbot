<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\AbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharactersDataReader;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharactersDataWriter;
use CarloNicora\Minimalism\Raw\Data\Objects\Character;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Enums\RawDocument;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Enums\RawTrait;
use CarloNicora\Minimalism\Raw\Factories\CommandOptionsFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\ApplicationCommands\ApplicationCommand;
use CarloNicora\Minimalism\Raw\Services\Discord\ApplicationCommands\ApplicationCommandOption;
use CarloNicora\Minimalism\Raw\Services\Discord\Enums\ApplicationCommandOptionType;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ApplicationCommandInterface;
use Exception;
use RuntimeException;

class CharacterCommand extends AbstractCommand
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

        if (
            $this->request->getCharacter() === null &&
            !$this->request->getPayload()?->hasParameter(PayloadParameter::Create) &&
            !$this->request->getPayload()?->hasParameter(PayloadParameter::List)){
            throw new RuntimeException(RawError::CharacterNotSpecified->getMessage());
        }

        if ($this->request->getCharacter() !== null && $this->request->getPayload()?->hasParameter(PayloadParameter::Create)){
            throw new RuntimeException(RawError::CharacterAlreadyExisting->getMessage());
        }

        if ($this->request->getPayload()?->hasParameter(PayloadParameter::List)){
            $this->getCharactersList();
        } else {
            $this->setCharacter();
        }

        return $this->response;
    }

    /**
     * @throws Exception
     */
    private function getCharactersList(
    ): void
    {
        $this->response->meta->add('list', true);

        /** @var CharactersDataReader $readCharacter */
        $readCharacter = MinimalismObjectsFactory::create(CharactersDataReader::class);

        $characters = $readCharacter->byServerId(
            serverId: $this->request->getServer()?->getId(),
            isGM: $this->request->isGM(),
        );

        foreach ($characters ?? [] as $character){
            $characterResource = new ResourceObject(
                type: RawDocument::Character->value,
                id: $character->getId(),
            );

            $characterResource->attributes->add('name', $character->getName() ?? $character->getShortname());
            $characterResource->attributes->add('shortName', $character->getShortname());
            $characterResource->attributes->add('description', $character->getDescription() ?? '');
            $characterResource->attributes->add('thumbnail', $character->getThumbnail() ?? '');
            $characterResource->attributes->add('isNPC',$character->isNPC());

            if ($this->request->getCharacter()?->getId() !== $character->getId() ){
                $characterResource->attributes->add('isMe',false);
            } else {
                $characterResource->attributes->add('isMe',true);
            }

            $this->response->addResource(
                resource: $characterResource,
            );
        }
    }

    /**
     * @param PayloadParameter $parameter
     * @return bool
     */
    private function updateField(
        PayloadParameter $parameter,
    ): bool
    {
        $value = $this->request->getPayload()?->getParameter($parameter);

        if ($value === null){
            return false;
        }

        switch ($parameter) {
            case PayloadParameter::Name:
                $this->request->getCharacter()?->setName($value);
                break;
            case PayloadParameter::Description:
                $this->request->getCharacter()?->setDescription($value);
                break;
            case PayloadParameter::Thumbnail:
                if (!str_starts_with(strtolower($value), 'http')){
                    throw new RuntimeException(RawError::InvalidThumbnailLink->getMessage());
                }
                if (!in_array(strtolower(substr($value, strlen($value) - 4)), ['.jpg', '.png'])){
                    throw new RuntimeException(RawError::InvalidThumbnailLink->getMessage());
                }
                $this->request->getCharacter()?->setThumbnail($value);
                break;
            case PayloadParameter::Body:
                if ($value < 0 || $value > 20){
                    throw new RuntimeException(RawError::InvalidTraitTraitValue->getMessage() . ' (' . PayloadParameter::Body->value . ')');
                }
                $this->request->getCharacter()?->setBody($value);
                break;
            case PayloadParameter::Mind:
                if ($value < 0 || $value > 20){
                    throw new RuntimeException(RawError::InvalidTraitTraitValue->getMessage() . ' (' . PayloadParameter::Mind->value . ')');
                }
                $this->request->getCharacter()?->setMind($value);
                break;
            case PayloadParameter::Spirit:
                if ($value < 0 || $value > 20){
                    throw new RuntimeException(RawError::InvalidTraitTraitValue->getMessage() . ' (' . PayloadParameter::Spirit->value . ')');
                }
                $this->request->getCharacter()?->setSpirit($value);
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    private function setCharacter(
    ): void
    {
        $updated = false;

        /** @noinspection RepetitiveMethodCallsInspection */
        if ($this->request->getPayload()?->hasParameter(PayloadParameter::Create) && $this->request->getPayload()?->hasParameter(PayloadParameter::Name)) {
            /** @var CharactersDataReader $readCharacter */
            $readCharacter = MinimalismObjectsFactory::create(CharactersDataReader::class);

            $character = null;
            try {
                $character = $readCharacter->byServerIdShortname(
                    serverId: $this->request->getServer()?->getId(),
                    shortname: $this->request->getPayload()?->getParameter(PayloadParameter::Name),
                );
            } catch (Exception) {
            }

            if ($character !== null) {
                throw new RuntimeException(RawError::CharacterAlreadyExisting->getMessage());
            }

            $this->request->setCharacter(
                new Character(
                    serverId: $this->request->getServer()?->getId(),
                    shortname: $this->request->getPayload()?->getParameter(PayloadParameter::Name),
                )
            );

            if ($this->request->isGM()){
                $this->request->getCharacter()?->setAsNPC();
            } else {
                $this->request->getCharacter()?->setUserId(userId: $this->request->getPayload()?->getUser()->getId());
            }
            $updated = true;
        }

        $updated = $this->updateField(PayloadParameter::Name) || $updated;
        $updated = $this->updateField(PayloadParameter::Description) || $updated;
        $updated = $this->updateField(PayloadParameter::Thumbnail) || $updated;
        $updated = $this->updateField(PayloadParameter::Body) || $updated;
        $updated = $this->updateField(PayloadParameter::Mind) || $updated;
        $updated = $this->updateField(PayloadParameter::Spirit) || $updated;

        if ($updated) {
            /** @var CharactersDataWriter $writeCharacter */
            $writeCharacter = MinimalismObjectsFactory::create(CharactersDataWriter::class);
            if ($this->request->getCharacter()?->isNew()) {
                $this->request->setCharacter(
                    $writeCharacter->insert(
                        character: $this->request->getCharacter()??throw new RuntimeException('error here')
                    )
                );
            } else {
                $writeCharacter->update([$this->request->getCharacter()]);
            }
        }

        $this->response->addResource(
            $this->getCharacterResource(
                updated: $updated,
            )
        );
    }

    /**
     * @param bool $updated
     * @return ResourceObject
     * @throws Exception
     */
    public function getCharacterResource(
        bool $updated,
    ): ResourceObject
    {
        $character = new ResourceObject(
            type: RawDocument::Character->value,
            id: $this->request->getCharacter()?->getId(),
        );
        $character->meta->add('updated', $updated);

        /** @var AbilitiesDataReader $readAbility */
        $readAbility = MinimalismObjectsFactory::create(AbilitiesDataReader::class);
        $abilities = $readAbility->byCharacterIdSettingIdExtended(
            characterId: $this->request->getCharacter()?->getId(),
            settingId: $this->request->getServer()?->getSettingId(),
        );

        $character->attributes->add('name', $this->request->getCharacter()?->getName() ?? $this->request->getCharacter()?->getShortname());
        $character->attributes->add('description', $this->request->getCharacter()?->getDescription() ?? '');
        $character->attributes->add('thumbnail', $this->request->getCharacter()?->getThumbnail() ?? '');

        $character->attributes->add('bonus', $this->request->getCharacter()?->getBonus() ?? 0);
        $character->attributes->add('damages', $this->request->getCharacter()?->getDamages() ?? 0);
        $character->attributes->add('lifePoints', 30 + ($this->request->getCharacter()?->getBody() ?? 0) - ($this->request->getCharacter()?->getDamages() ?? 0));

        foreach (RawTrait::cases() as $trait) {
            $character->attributes->add($trait->value, $this->request->getCharacter()?->getTraitValue($trait));
        }

        foreach ($abilities as $ability) {
            $abilityResource = new ResourceObject(
                type: RawDocument::Ability->value,
                id: $ability['abilityId'],
            );
            $abilityName = $ability['fullName'];
            if ($ability['specialisation'] !== '/' && $ability['specialisation'] !== null) {
                $abilityName .= '/' . $ability['specialisation'];
            }
            $abilityResource->attributes->add(
                name: 'name',
                value: $abilityName,
            );
            $abilityResource->attributes->add(
                name: 'value',
                value: $ability['value'],
            );
            $abilityResource->attributes->add(
                name: 'hasBeenUsed',
                value: !in_array($ability['used'], [null, 0, false], true),
            );
            $character->relationship(RawTrait::from($ability['trait'])->value)->resourceLinkage->add($abilityResource);

            if ($ability['wasUpdated'] === 1){
                $character->relationship('updatedAbilities')->resourceLinkage->add($abilityResource);
            }
        }

        return $character;
    }

    /**
     * @param int|null $serverId
     * @return ApplicationCommandInterface
     */
    public function getDefinition(
        ?int $serverId=null,
    ): ApplicationCommandInterface
    {
        $response = new ApplicationCommand(
            id: RawCommand::Character->value,
            applicationId: '??',
            name: RawCommand::Character->value,
            description: 'Manage your character',
        );

        $response->addOption(
            new ApplicationCommandOption(
                type: ApplicationCommandOptionType::SUB_COMMAND,
                name: PayloadParameter::List->value,
                description: 'List all the characters of a campaign',
            )
        );

        $response->addOption(CommandOptionsFactory::getCharacterDetailsCommand());
        $response->addOption(CommandOptionsFactory::getCharacterCreationCommand());
        $response->addOption(CommandOptionsFactory::getCharacterVariablesCommand());

        return $response;
    }
}