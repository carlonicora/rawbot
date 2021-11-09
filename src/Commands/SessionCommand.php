<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharacterAbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharactersDataReader;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharacterAbilitiesDataWriter;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharactersDataWriter;
use CarloNicora\Minimalism\Raw\Data\DataWriters\ServersDataWriter;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawDocument;
use CarloNicora\Minimalism\Raw\Exceptions\ErrorException;
use CarloNicora\Minimalism\Raw\Helpers\DiceRoller;
use Exception;
use RuntimeException;

class SessionCommand extends AbstractCommand
{
    /**
     * @return Document
     * @throws Exception
     */
    public function execute(
    ): Document
    {
        if (!$this->request->isGM()){
            throw new RuntimeException('Only the GM can manage the sessions!');
        }

        if ($this->request->getPayload()->getParameter(PayloadParameter::Command) === 'start') {
            $this->startSession();
        } else {
            $this->endSession();
        }

        /** @var ServersDataWriter $writeServer */
        $writeServer = MinimalismObjectsFactory::create(ServersDataWriter::class);
        $writeServer->upload($this->request->getServer());

        return $this->response;
    }

    /**
     *
     * @throws Exception
     */
    private function startSession(
    ): void
    {
        if ($this->request->getServer()->isInSession()){
            throw new ErrorException('A session is already running');
        }

        $this->request->getServer()->startSession();

        /** @var CharacterAbilitiesDataWriter $writeCharacterAbilities */
        $writeCharacterAbilities = MinimalismObjectsFactory::create(CharacterAbilitiesDataWriter::class);

        $writeCharacterAbilities->resetUsage(
            serverId: $this->request->getServer()->getId(),
        );

        $this->response->addResource(
            new ResourceObject(
                type: RawDocument::SessionStart->value,
            )
        );
    }

    /**
     * @throws Exception
     */
    private function endSession(
    ): void
    {
        if (!$this->request->getServer()->isInSession()){
            throw new ErrorException('You are not in a session.');
        }

        $this->request->getServer()->endSession();

        $resource =new ResourceObject(
            type: RawDocument::SessionEnd->value,
        );

        /** @var CharactersDataReader $readCharacter */
        $readCharacter = MinimalismObjectsFactory::create(CharactersDataReader::class);
        /** @var CharacterAbilitiesDataReader $readCharacterAbility */
        $readCharacterAbility = MinimalismObjectsFactory::create(CharacterAbilitiesDataReader::class);
        /** @var CharacterAbilitiesDataWriter $writeCharacterAbility */
        $writeCharacterAbility = MinimalismObjectsFactory::create(CharacterAbilitiesDataWriter::class);
        /** @var CharactersDataWriter $writeCharacter */
        $writeCharacter = MinimalismObjectsFactory::create(CharactersDataWriter::class);

        $characters = $readCharacter->byServerId(serverId: $this->request->getServer()->getId());


        foreach ($characters as $character){
            $characterResource = null;

            $usedAbilities = $readCharacterAbility->usedByCharacterId(characterId: $character->getId());

            $character->addBonus(3);

            if (!$character->isNPC()) {
                $characterResource = new ResourceObject(
                    type: 'characterAdvancement',
                    id: $character->getId(),
                );
                $characterResource->attributes->add('name', $character->getName() ?? $character->getShortname());
                $characterResource->meta->add('bonus', 3);
                $characterResource->meta->add('totalBonus', $character->getBonus());
            }

            $atLeastOneAbilityUpdated = false;
            foreach ($usedAbilities as $usedAbility){
                $delta = 0;
                $roll = DiceRoller::roll(100);
                $bonus = DiceRoller::calculateBonus(
                    $usedAbility->getValue(),
                    $character->getTraitValue($usedAbility->getAbility()->getTrait()),
                    $roll,
                    $delta
                );

                if ($bonus > 0) {
                    $usedAbility->increaseValue($bonus);
                    $usedAbility->markAsUpdated();
                    $atLeastOneAbilityUpdated = true;
                }

                if (!$character->isNPC()) {
                    $usedAbilityResource = new ResourceObject(
                        type: 'ability',
                        id: $usedAbility->getAbilityId(),
                    );
                    $usedAbilityResource->meta->add('updated', ($bonus !== 0));
                    $usedAbilityResource->meta->add('roll', $roll);
                    $usedAbilityResource->meta->add('bonus', $bonus);

                    $abilityName = $usedAbility->getAbility()->getFullName();
                    if ($usedAbility->getSpecialisation() !== '/'){
                        $abilityName .= '/' . $usedAbility->getSpecialisation();
                    }

                    $usedAbilityResource->attributes->add(
                        name: 'name',
                        value: $abilityName,
                    );
                    $usedAbilityResource->attributes->add('value', $usedAbility->getValue());
                    $characterResource->relationship('abilities')->resourceLinkage->add(
                        resource: $usedAbilityResource,
                    );
                }
            }

            if ($atLeastOneAbilityUpdated) {
                $writeCharacterAbility->update($usedAbilities);
            }

            if (!$character->isNPC()) {
                $resource->relationship('characters')->resourceLinkage->add(
                    $characterResource
                );
            }
        }

        $writeCharacter->update($characters);

        $this->response->addResource(
            $resource
        );
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
            'name' => 'session',
            'description' => 'Manage a session. (Only the GM is allowed to use this)',
            'options' => [
                [
                    'type' => 3,
                    'name' => 'command',
                    'description' => 'Do you want to start or end the session?',
                    'required' => true,
                    'choices' => [
                        [
                            'name' => 'start',
                            'value' => 'start'
                        ],
                        [
                            'name' => 'end',
                            'value' => 'end'
                        ],
                    ]
                ],
            ],
        ];
    }
}