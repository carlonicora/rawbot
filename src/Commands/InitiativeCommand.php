<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharacterAbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharactersDataReader;
use CarloNicora\Minimalism\Raw\Enums\CriticalRoll;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Enums\RawTrait;
use CarloNicora\Minimalism\Raw\Helpers\DiceRoller;
use CarloNicora\Minimalism\Raw\Services\Discord\ApplicationCommands\ApplicationCommand;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ApplicationCommandInterface;
use Exception;
use RuntimeException;

class InitiativeCommand extends AbstractCommand
{
    /**
     * @return Document
     * @throws Exception
     */
    public function execute(
    ): Document
    {
        if ($this->request->getServer() === null) {
            throw new RuntimeException(RawError::CampaignNotInitialised->getMessage());
        }

        if (!$this->request->getServer()->isInSession()){
            throw new RuntimeException(RawError::NotInSession->getMessage());
        }

        if (!$this->request->isGM()){
            throw new RuntimeException('Only the GM can roll initiative!');
        }

        /** @var CharactersDataReader $readCharacter */
        $readCharacter = MinimalismObjectsFactory::create(CharactersDataReader::class);
        /** @var CharacterAbilitiesDataReader $readCharacterAbility */
        $readCharacterAbility = MinimalismObjectsFactory::create(CharacterAbilitiesDataReader::class);

        $characters = $readCharacter->byServerId(serverId: $this->request->getServer()->getId(), isGM: false);

        $charactersResults = [];

        foreach ($characters ?? [] as $character) {
            $characterResource = $character->generateResourceObject();

            $characterAbility = new ResourceObject(
                type: 'characterAbility',
            );

            try {
                $bestInitiativeAbility = $readCharacterAbility->readBestInitiativeAbility($character->getId());

                $value = $bestInitiativeAbility['value'] + $character->getTraitValue(RawTrait::from($bestInitiativeAbility['trait']));

                $characterAbility->attributes->add('ability', $bestInitiativeAbility['fullName']);
                $characterAbility->attributes->add('value', $value);
            } catch (Exception) {
                $value = max($character->getBody(), $character->getMind(), $character->getSpirit());

                $characterAbility->attributes->add('ability', 'trait');
                $characterAbility->attributes->add('value', $value);
            }

            $crititical = CriticalRoll::None;
            $roll = DiceRoller::roll(20, $crititical);
            if ($crititical === CriticalRoll::Success){
                $roll += 20;
            } elseif ($crititical === CriticalRoll::Failure){
                $roll -= 21;
            }

            $initiative = $value + $roll;

            $characterAbility->attributes->add('roll', $roll);
            $characterAbility->attributes->add('critical', $crititical->value);
            $characterAbility->attributes->add('initiative', $initiative);

            $characterResource->relationship('initiative')->resourceLinkage->add($characterAbility);

            $charactersResults[$character->getId()] = [
                'initiative' => $initiative,
                'resource' => $characterResource,
            ];
        }

        usort($charactersResults, [$this, 'sortByInitiative']);

        foreach ($charactersResults as $charactersResult) {
            $this->response->addResource($charactersResult['resource']);
        }

        return $this->response;
    }

    /**
     * @param array $characterA
     * @param array $characterB
     * @return int
     */
    private function sortByInitiative(
        array $characterA,
        array $characterB,
    ): int
    {
        return $characterA['initiative'] - $characterB['initiative'];
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
        return new ApplicationCommand(
            id: RawCommand::Initiative->value,
            applicationId: '??',
            name: RawCommand::Initiative->value,
            description: '[GM only] Roll Initiative!',
        );
    }
}