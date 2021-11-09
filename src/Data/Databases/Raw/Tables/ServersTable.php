<?php
namespace CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class ServersTable extends AbstractMySqlTable
{
    /** @var string  */
    protected static string $tableName = 'servers';

    /** @var array  */
    protected static array $fields = [
        'serverId'          => FieldInterface::INTEGER
                            +  FieldInterface::PRIMARY_KEY
                            +  FieldInterface::AUTO_INCREMENT,
        'discordServerId'   => FieldInterface::STRING,
        'discordUserId'     => FieldInterface::STRING,
        'campaignName'      => FieldInterface::STRING,
        'inSession'         => FieldInterface::INTEGER
    ];

    /**
     * @param string $discordServerId
     * @return array
     * @throws Exception
     */
    public function readByDiscordServerId(
        string $discordServerId,
    ): array
    {
        $this->sql = 'SELECT * FROM servers WHERE discordServerId=?;';
        $this->parameters = ['s',$discordServerId];

        return $this->functions->runRead();
    }
}