<?php
namespace CarloNicora\Minimalism\Raw\Data\DataReaders;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables\CharactersTable;
use CarloNicora\Minimalism\Raw\Data\Objects\Character;
use Exception;

class CharactersDataReader extends AbstractLoader
{
    /**
     * @param int $serverId
     * @param string $discordUserId
     * @return Character
     * @throws Exception
     */
    public function byServerIdDiscordUserId(
        int $serverId,
        string $discordUserId,
    ): Character
    {
        /** @see CharactersTable::readByServerIdDiscordUserId() */
        $recordset = $this->data->read(
            tableInterfaceClassName: CharactersTable::class,
            functionName: 'readByServerIdDiscordUserId',
            parameters: [$serverId, $discordUserId],
        );

        return $this->returnSingleObject(
            recordset: $recordset,
            objectType: Character::class,
        );
    }

    /**
     * @param int $serverId
     * @return Character[]
     * @throws Exception
     */
    public function byServerId(
        int $serverId,
    ): array
    {
        /** @see CharactersTable::readByServerId() */
        $recordset = $this->data->read(
            tableInterfaceClassName: CharactersTable::class,
            functionName: 'readByServerId',
            parameters: [$serverId],
        );

        return $this->returnObjectArray(
            recordset: $recordset,
            objectType: Character::class,
        );
    }

    /**
     * @param int $serverId
     * @param string $shortname
     * @return Character
     * @throws Exception
     */
    public function byServerIdShortname(
        int $serverId,
        string $shortname,
    ): Character
    {
        /** @see CharactersTable::readByServerIdShortname() */
        $recordset = $this->data->read(
            tableInterfaceClassName: CharactersTable::class,
            functionName: 'readByServerIdShortname',
            parameters: [$serverId, $shortname],
        );

        return $this->returnSingleObject(
            recordset: $recordset,
            objectType: Character::class,
        );
    }
}