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

    /**
     * @param int $characterId
     * @param int $settingId
     * @return array
     * @throws Exception
     */
    public function readByCharacterIdSettingIdExtended(
        int $characterId,
        int $settingId,
    ): array
    {
        $this->sql = 'SELECT abilities.abilityId,'
            . ' abilities.trait,'
            . ' abilities.fullName,'
            . ' characterAbilities.specialisation,'
            . ' characterAbilities.value,'
            . ' characterAbilities.used'
            . ' FROM abilities'
            . ' LEFT JOIN characterAbilities'
            . ' ON abilities.abilityId=characterAbilities.abilityId'
            . ' AND characterAbilities.characterId=?'
            . ' WHERE abilities.settingId=?'
            . ' ORDER BY abilities.trait,'
            . ' abilities.fullName;';
        $this->parameters = ['ii', $characterId, $settingId];

        return $this->functions->runRead();
    }
}