<?php
namespace CarloNicora\Minimalism\Raw\Helpers;

use CarloNicora\Minimalism\Raw\Enums\CriticalRoll;
use Exception;

class DiceRoller
{
    /**
     * @param int $diceSides
     * @param CriticalRoll $criticalRoll
     * @return int
     */
    public static function roll(int $diceSides, CriticalRoll &$criticalRoll=CriticalRoll::None): int
    {
        $result = self::simpleRoll($diceSides);

        $criticalRoll = match ($result) {
            1 => CriticalRoll::Failure,
            $diceSides => CriticalRoll::Success,
            default => CriticalRoll::None,
        };

        return $result;
    }

    /**
     * @param int $diceSides
     * @return int
     */
    public static function simpleRoll(int $diceSides): int
    {
        try {
            return random_int(1, $diceSides);
        } catch (Exception $e) {
            return $diceSides / 10 * 4;
        }
    }

    /**
     * @param int $ability
     * @param int $trait
     * @param int $roll
     * @param int $delta
     * @return int
     */
    public static function calculateBonus(
        int $ability,
        int $trait,
        int $roll,
        int &$delta
    ): int
    {
        $delta = $roll-$ability-$trait;

        $response = 0;

        if ($delta >= 0){
            $response = (int)($delta/20)+1;
        }

        if ($roll === 100){
            $response *= 2;
        }

        return $response;
    }
}