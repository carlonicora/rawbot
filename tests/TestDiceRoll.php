<?php
namespace CarloNicora\Minimalism\Raw\Tests;

use Exception;
use PHPUnit\Framework\TestCase;

class TestDiceRoll extends TestCase
{
    public function data(): array
    {
        return [
            [true, '1d20'],
            [false, '20'],
            [false, 'ad20'],
            [false, 'd20'],
        ];
    }

    /**
     * @dataProvider data
     */
    public function testDice(
        bool $expectedResult,
        string $dice,
    ): void
    {
        $diceParameters = array_map('intval', explode('d', $dice));

        if (count($diceParameters) === 2 && $diceParameters[0] > 0 && $diceParameters[1] > 0){
            [$quantity, $sides] = $diceParameters;
            self::assertTrue($expectedResult);
        } else {
            self::assertFalse($expectedResult);
        }
    }
}