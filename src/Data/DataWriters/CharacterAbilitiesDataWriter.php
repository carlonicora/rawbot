<?php
namespace CarloNicora\Minimalism\Raw\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables\CharacterAbilitiesTable;
use CarloNicora\Minimalism\Raw\Data\Objects\CharacterAbility;

class CharacterAbilitiesDataWriter extends AbstractLoader
{
    /**
     * @param CharacterAbility[] $characterAbilities
     */
    public function update(
        array $characterAbilities,
    ): void
    {
        $abilities = [];

        foreach ($characterAbilities as $characterAbility){
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
        /** @noinspection UnusedFunctionResultInspection */
        $this->data->run(
            tableInterfaceClassName: CharacterAbilitiesTable::class,
            functionName: 'updateResetUsage',
            parameters: [$serverId],
        );
    }
}