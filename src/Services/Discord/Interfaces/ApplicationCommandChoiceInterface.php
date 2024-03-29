<?php
namespace CarloNicora\Minimalism\Raw\Services\Discord\Interfaces;

interface ApplicationCommandChoiceInterface extends ExportableInterface
{
    /**
     * @param string $name
     * @param string|int|float $value
     */
    public function __construct(
        string $name,
        string|int|float $value,
    );
}