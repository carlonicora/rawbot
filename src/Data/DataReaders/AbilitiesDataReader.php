<?php
namespace CarloNicora\Minimalism\Raw\Data\DataReaders;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables\AbilitiesTable;
use CarloNicora\Minimalism\Raw\Data\Objects\Ability;
use Exception;

class AbilitiesDataReader extends AbstractLoader
{
    /**
     * @param int $abilityId
     * @return Ability
     * @throws Exception
     */
    public function byId(
        int $abilityId,
    ): Ability
    {
        /** @see AbilitiesTable::byId() */
        $recordset = $this->data->read(
            tableInterfaceClassName: AbilitiesTable::class,
            functionName: 'byId',
            parameters: [$abilityId],
        );

        return $this->returnSingleObject(
            recordset: $recordset,
            objectType: Ability::class,
        );
    }

    /**
     * @param string $name
     * @return array
     * @throws Exception
     */
    public function byName(
        string $name,
    ): array
    {
        /** @see AbilitiesTable::readByName() */
        $recordset = $this->data->read(
            tableInterfaceClassName: AbilitiesTable::class,
            functionName: 'readByName',
            parameters: [$name],
        );

        return $this->returnSingleValue($recordset);
    }

    /**
     * @return array
     */
    public function all(
    ): array
    {
        /** @see AbilitiesTable::all() */
        return $this->data->read(
            tableInterfaceClassName: AbilitiesTable::class,
            functionName: 'all',
            parameters: [],
        );
    }
}