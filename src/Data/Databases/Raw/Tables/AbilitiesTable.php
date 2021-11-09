<?php
namespace CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class AbilitiesTable extends AbstractMySqlTable
{
    /** @var string  */
    protected static string $tableName = 'abilities';

    /** @var array  */
    protected static array $fields = [
        'abilityId'         => FieldInterface::INTEGER
                            +  FieldInterface::PRIMARY_KEY
                            +  FieldInterface::AUTO_INCREMENT,
        'trait'             => FieldInterface::STRING,
        'name'              => FieldInterface::STRING,
        'canChallenge'      => FieldInterface::INTEGER,
        'canBeOpposed'      => FieldInterface::INTEGER,
        'definesInitiative' => FieldInterface::INTEGER
    ];

    /**
     * @param string $name
     * @return array
     * @throws Exception
     */
    public function readByName(
        string $name,
    ): array
    {
        $this->sql = 'SELECT * FROM abilities WHERE name=?;';
        $this->parameters = ['s', $name];

        return $this->functions->runRead();
    }
}