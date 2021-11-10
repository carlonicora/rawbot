<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\AbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharactersDataWriter;
use CarloNicora\Minimalism\Raw\Data\Objects\Character;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Enums\RawDocument;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Enums\RawTrait;
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

        if ($this->request->getCharacter() === null && !$this->request->getPayload()->hasParameter(PayloadParameter::Create)){
            throw new RuntimeException(RawError::CharacterNotSpecified->getMessage());
        }

        $updated = false;

        if (($newCharacter=$this->request->getPayload()->getParameter(PayloadParameter::Create)) !== null){
            $this->request->setCharacter(
                new Character(
                    serverId: $this->request->getServer()->getId(),
                    userId: $this->request->getPayload()->getUser()->getId(),
                    shortname: $newCharacter,
                )
            );
            $updated = true;
        }

        if (($name = $this->request->getPayload()->getParameter(PayloadParameter::Name)) !== null){
            $this->request->getCharacter()->setName($name);
            $updated = true;
        }

        if (($description = $this->request->getPayload()->getParameter(PayloadParameter::Description)) !== null){
            $this->request->getCharacter()->setDescription($description);
            $updated = true;
        }

        if (($thumbnail = $this->request->getPayload()->getParameter(PayloadParameter::Thumbnail)) !== null){
            $this->request->getCharacter()->setThumbnail($thumbnail);
            $updated = true;
        }

        if (($body = $this->request->getPayload()->getParameter(PayloadParameter::Body)) !== null){
            $this->request->getCharacter()->setBody($body);
            $updated = true;
        }

        if (($mind = $this->request->getPayload()->getParameter(PayloadParameter::Mind)) !== null){
            $this->request->getCharacter()->setMind($mind);
            $updated = true;
        }

        if (($spirit = $this->request->getPayload()->getParameter(PayloadParameter::Spirit)) !== null){
            $this->request->getCharacter()->setSpirit($spirit);
            $updated = true;
        }

        if ($updated) {
            /** @var CharactersDataWriter $writeCharacter */
            $writeCharacter = MinimalismObjectsFactory::create(CharactersDataWriter::class);
            if ($this->request->getCharacter()->isNew()){
                $this->request->setCharacter(
                    $writeCharacter->insert(
                        character: $this->request->getCharacter()
                    )
                );
            } else {
                $writeCharacter->update([$this->request->getCharacter()]);
            }
        }

        $character = new ResourceObject(
            type: RawDocument::Character->value,
            id: $this->request->getCharacter()->getId(),
        );
        $character->meta->add('updated', $updated);

        /** @var AbilitiesDataReader $readAbility */
        $readAbility = MinimalismObjectsFactory::create(AbilitiesDataReader::class);
        $abilities = $readAbility->byCharacterIdSettingIdExtended(
            characterId: $this->request->getCharacter()->getId(),
            settingId: $this->request->getServer()->getSettingId(),
        );

        $character->attributes->add('name', $this->request->getCharacter()->getName()??$this->request->getCharacter()->getShortname());
        $character->attributes->add('description', $this->request->getCharacter()->getDescription()??'');
        $character->attributes->add('thumbnail', $this->request->getCharacter()->getThumbnail()??'');

        $character->attributes->add('bonus', $this->request->getCharacter()->getBonus()??0);
        $character->attributes->add('damages', $this->request->getCharacter()->getDamages()??0);
        $character->attributes->add('lifePoints', 30 + $this->request->getCharacter()->getBody()??0 - $this->request->getCharacter()->getDamages()??0);


        foreach (RawTrait::cases() as $trait){
            $character->attributes->add($trait->value,$this->request->getCharacter()->getTraitValue($trait));
        }

        foreach ($abilities as $ability){
            $abilityResource = new ResourceObject(
                type: RawDocument::Ability->value,
                id: $ability['abilityId'],
            );
            $abilityName = $ability['fullName'];
            if ($ability['specialisation'] !== '/' && $ability['specialisation'] !== null){
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
                value: $ability['used'] !== null && $ability['used'] !== 0 && $ability['used'] !== false,
            );
            $character->relationship(RawTrait::from($ability['trait'])->value)->resourceLinkage->add($abilityResource);
        }

        $this->response->addResource(
            $character
        );

        return $this->response;
    }

    /**
     * @param int|null $serverId
     * @return array
     */
    public function getDefinition(
        ?int $serverId=null,
    ): array
    {
        return [
            'name' => RawCommand::Character->value,
            'description' => 'Manage your character',
            'options' => [
                [
                    'type' => 3,
                    'name' => PayloadParameter::Create->value,
                    'description' => 'Create s new character by defining a unique short name (use the player first name!)',
                    'required' => false,
                ],[
                    'type' => 3,
                    'name' => PayloadParameter::Name->value,
                    'description' => 'Change the name of your character',
                    'required' => false,
                ],[
                    'type' => 3,
                    'name' => PayloadParameter::Description->value,
                    'description' => 'Change the description of your character',
                    'required' => false,
                ],[
                    'type' => 3,
                    'name' => PayloadParameter::Thumbnail->value,
                    'description' => 'Change the thumbnail of your character. You need to use a valid URL to an image (NOT A WEB PAGE)',
                    'required' => false,
                ],[
                    'type' => 4,
                    'name' => PayloadParameter::Body->value,
                    'description' => 'Change the body trait value of your character',
                    'required' => false,
                ],[
                    'type' => 4,
                    'name' => PayloadParameter::Mind->value,
                    'description' => 'Change the mind trait value of your character',
                    'required' => false,
                ],[
                    'type' => 4,
                    'name' => PayloadParameter::Spirit->value,
                    'description' => 'Change the spirit trait value of your character',
                    'required' => false,
                ],
            ],
        ];
    }
}