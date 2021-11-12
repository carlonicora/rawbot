<?php
namespace CarloNicora\Minimalism\Raw\Data\Databases\Raw\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class CharacterAbilitiesTable extends AbstractMySqlTable
{
    /** @var string  */
    protected static string $tableName = 'characterAbilities';

    /** @var array  */
    protected static array $fields = [
        'characterId'       => FieldInterface::INTEGER
                            +  FieldInterface::PRIMARY_KEY,
        'abilityId'         => FieldInterface::INTEGER
                            +  FieldInterface::PRIMARY_KEY,
        'specialisation'    => FieldInterface::STRING
                            +  FieldInterface::PRIMARY_KEY,
        'value'             => FieldInterface::INTEGER,
        'used'              => FieldInterface::INTEGER,
        'wasUpdated'        => FieldInterface::INTEGER
    ];

    /**
     * @param int $characterId
     * @param int $abilityId
     * @param string $specialisation
     * @return array
     * @throws Exception
     */
    public function readByCharacterIdAbilityIdSpecialisation(
        int $characterId,
        int $abilityId,
        string $specialisation,
    ): array
    {
        $this->sql = 'SELECT *'
            . ' FROM characterAbilities WHERE characterId=? AND abilityId=? AND specialisation=?;';
        $this->parameters = ['iis', $characterId, $abilityId, $specialisation];

        return $this->functions->runRead();
    }

    /**
     * @param int $serverId
     * @throws Exception
     */
    public function updateResetUsage(
        int $serverId
    ): void
    {
        $this->sql = 'UPDATE characterAbilities'
            . ' JOIN characters ON characters.characterId=characterAbilities.characterId'
            . ' SET	wasUpdated=?, used=?'
            . ' WHERE characters.serverId=?;';
        $this->parameters = ['iii', 0, 0, $serverId];

        $this->functions->runSql();
    }

    /**
     * @param int $characterId
     * @return array
     * @throws Exception
     */
    public function readUsedByCharacterId(
        int $characterId,
    ): array
    {
        $this->sql = 'SELECT *'
            . ' FROM characterAbilities WHERE characterId=? AND used=?;';
        $this->parameters = ['ii', $characterId, 1];

        return $this->functions->runRead();
    }

    /**
     * @param int $characterId
     * @return array
     * @throws Exception
     */
    public function readBestCharacterInitiativeAbility(
        int $characterId,
    ): array
    {
        $this->sql = 'SELECT'
            . ' characterAbilities.*,'
            . ' abilities.trait,'
            . ' abilities.fullName'
            . ' FROM characterAbilities'
            . ' JOIN abilities ON characterAbilities.abilityId=abilities.abilityId'
            . ' WHERE abilities.definesInitiative=?'
            . ' AND characterAbilities.characterId=?'
            . ' ORDER BY characterAbilities.value DESC'
            . ' LIMIT 0,1;';
        $this->parameters = ['ii', 1, $characterId];

        return $this->functions->runRead();
    }
}