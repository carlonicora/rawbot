<?php
namespace CarloNicora\Minimalism\Raw\Services\Discord\Interfaces;

interface ExportableInterface
{
    /**
     * @return array
     */
    public function export(): array;
}