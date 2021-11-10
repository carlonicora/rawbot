<?php
namespace CarloNicora\Minimalism\Raw\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables\CharactersTable;
use CarloNicora\Minimalism\Raw\Data\Objects\Character;
use Exception;

class CharactersDataWriter extends AbstractLoader
{
    /**
     * @param Character[] $characters
     */
    public function update(
        array $characters,
    ): void
    {
        $charactersRecordset = [];

        foreach ($characters as $character){
            $charactersRecordset[] = $character->export();
        }

        $this->data->update(
            tableInterfaceClassName: CharactersTable::class,
            records: $charactersRecordset,
        );
    }

    /**
     * @param Character $character
     * @return Character
     * @throws Exception
     */
    public function insert(
        Character $character
    ): Character
    {
        $record = $this->data->insert(
            tableInterfaceClassName: CharactersTable::class,
            records: [$character->export()],
        );

        return $this->returnSingleObject(
            recordset: [$record],
            objectType: Character::class,
        );
    }
}