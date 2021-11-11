<?php
namespace CarloNicora\Minimalism\Raw\Interfaces;

use CarloNicora\JsonApi\Objects\ResourceObject;

interface ResurceGenerationInterface
{
    /**
     * @return ResourceObject
     */
    public function generateResourceObject(): ResourceObject;
}