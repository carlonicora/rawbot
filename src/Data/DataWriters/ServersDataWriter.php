<?php
namespace CarloNicora\Minimalism\Raw\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables\ServersTable;
use CarloNicora\Minimalism\Raw\Data\Objects\Server;

class ServersDataWriter extends AbstractLoader
{
    /**
     * @param Server $server
     */
    public function upload(
        Server $server,
    ): void
    {
        $this->data->update(
            tableInterfaceClassName: ServersTable::class,
            records: [$server->export()],
        );
    }
}