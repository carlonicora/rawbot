<?php
namespace CarloNicora\Minimalism\Raw\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables\CharactersTable;
use CarloNicora\Minimalism\Raw\Data\Objects\Character;

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
}