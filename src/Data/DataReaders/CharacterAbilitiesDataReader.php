<?php
namespace CarloNicora\Minimalism\Raw\Data\DataReaders;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables\CharacterAbilitiesTable;
use CarloNicora\Minimalism\Raw\Data\Objects\CharacterAbility;
use Exception;

class CharacterAbilitiesDataReader extends AbstractLoader
{
    /**
     * @param int $characterId
     * @param int $abilityId
     * @param string $specialisation
     * @return CharacterAbility
     * @throws Exception
     */
    public function byCharacterIdAbilityIdSpecialisation(
        int $characterId,
        int $abilityId,
        string $specialisation,
    ): CharacterAbility
    {
        /** @see CharacterAbilitiesTable::readByCharacterIdAbilityIdSpecialisation() */
        $recordset = $this->data->read(
            tableInterfaceClassName: CharacterAbilitiesTable::class,
            functionName: 'readByCharacterIdAbilityIdSpecialisation',
            parameters: [$characterId,$abilityId,$specialisation],
        );

        return $this->returnSingleObject(
            recordset: $recordset,
            objectType: CharacterAbility::class,
        );
    }

    /**
     * @param int $characterId
     * @return CharacterAbility[]
     */
    public function usedByCharacterId(
        int $characterId,
    ): array
    {
        /** @see CharacterAbilitiesTable::readUsedByCharacterId() */
        $recordset = $this->data->read(
            tableInterfaceClassName: CharacterAbilitiesTable::class,
            functionName: 'readUsedByCharacterId',
            parameters: [$characterId],
        );

        return $this->returnObjectArray(
            recordset: $recordset,
            objectType: CharacterAbility::class,
        );
    }

    /**
     * @param int $characterId
     * @return array
     * @throws Exception
     */
    public function readBestInitiativeAbility(
        int $characterId,
    ): array
    {
        /** @see CharacterAbilitiesTable::readBestCharacterInitiativeAbility() */
        $recordset = $this->data->read(
            tableInterfaceClassName: CharacterAbilitiesTable::class,
            functionName: 'readBestCharacterInitiativeAbility',
            parameters: [$characterId],
        );

        return $this->returnSingleValue($recordset);
    }
}