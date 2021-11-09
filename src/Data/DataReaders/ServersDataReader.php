<?php
namespace CarloNicora\Minimalism\Raw\Data\DataReaders;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables\ServersTable;
use CarloNicora\Minimalism\Raw\Data\Objects\Server;
use Exception;

class ServersDataReader extends AbstractLoader
{
    /**
     * @param string $discordServerId
     * @return Server
     * @throws Exception
     */
    public function byDiscordServerId(
        string $discordServerId,
    ): Server
    {
        /** @see ServersTable::readByDiscordServerId() */
        $records = $this->data->read(
            tableInterfaceClassName: ServersTable::class,
            functionName: 'readByDiscordServerId',
            parameters: [$discordServerId],
        );

        return $this->returnSingleObject(
            recordset: $records,
            objectType: Server::class,
        );
    }
}