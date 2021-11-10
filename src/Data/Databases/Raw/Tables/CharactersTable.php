<?php
namespace CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class CharactersTable extends AbstractMySqlTable
{
    /** @var string  */
    protected static string $tableName = 'characters';

    /** @var array  */
    protected static array $fields = [
        'characterId'                   => FieldInterface::INTEGER
                                        +  FieldInterface::PRIMARY_KEY
                                        +  FieldInterface::AUTO_INCREMENT,
        'serverId'                      => FieldInterface::INTEGER,
        'discordUserId'                 => FieldInterface::STRING,
        'discordUserName'               => FieldInterface::STRING,
        'isNPC'                         => FieldInterface::INTEGER,
        'shortName'                     => FieldInterface::STRING,
        'name'                          => FieldInterface::STRING,
        'body'                          => FieldInterface::INTEGER,
        'mind'                          => FieldInterface::INTEGER,
        'spirit'                        => FieldInterface::INTEGER,
        'bonusPoints'                   => FieldInterface::INTEGER,
        'damages'                       => FieldInterface::INTEGER,
        'description'                   => FieldInterface::STRING,
        'automaticallyAcceptChallenges' => FieldInterface::INTEGER,
        'thumbnail'                     => FieldInterface::STRING
    ];

    /**
     * @param int $serverId
     * @param string $discordUserId
     * @return array
     * @throws Exception
     */
    public function readByServerIdDiscordUserId(
        int $serverId,
        string $discordUserId,
    ): array
    {
        $this->sql = 'SELECT * FROM characters WHERE serverId=? AND discordUserId=?;';
        $this->parameters =['is', $serverId, $discordUserId];

        return $this->functions->runRead();
    }

    /**
     * @param int $serverId
     * @param bool $isGM
     * @return array
     * @throws Exception
     */
    public function readByServerId(
        int $serverId,
        bool $isGM,
    ): array
    {
        $this->sql = 'SELECT *'
            . ' FROM characters'
            . ' WHERE serverId=?';
        $this->parameters =['i', $serverId];

        if (!$isGM) {
            $this->sql .= ' AND isNPC=?';
            $this->parameters[0] .= 'i';
            $this->parameters[] = 0;
        }
        $this->sql .= ' ORDER BY isNPC, name, shortName;';

        return $this->functions->runRead();
    }

    /**
     * @param int $serverId
     * @param string $shortname
     * @return array
     * @throws Exception
     */
    public function readByServerIdShortname(
        int $serverId,
        string $shortname,
    ): array
    {
        $this->sql = 'SELECT * FROM characters WHERE serverId=? AND shortName=?;';
        $this->parameters =['is', $serverId, $shortname];

        return $this->functions->runRead();
    }
}