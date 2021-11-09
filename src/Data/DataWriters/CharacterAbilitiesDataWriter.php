<?php
namespace CarloNicora\Minimalism\Raw\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables\CharacterAbilitiesTable;
use CarloNicora\Minimalism\Raw\Data\Objects\CharacterAbility;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;

class CharacterAbilitiesDataWriter extends AbstractLoader
{
    /**
     * @param CharacterAbility[] $characterAbilitities
     */
    public function update(
        array $characterAbilitities,
    ): void
    {
        $abilities = [];

        foreach ($characterAbilitities as $characterAbility){
            $abilities[] = $characterAbility->export();
        }

        $this->data->update(
            tableInterfaceClassName: CharacterAbilitiesTable::class,
            records: $abilities,
        );
    }

    /**
     * @param int $serverId
     */
    public function resetUsage(
        int $serverId,
    ): void
    {
        /** @see CharacterAbilitiesTable::updateResetUsage() */
        $this->data->run(
            tableInterfaceClassName: CharacterAbilitiesTable::class,
            functionName: 'updateResetUsage',
            parameters: [$serverId],
        );
    }
}