<?php
namespace CarloNicora\Minimalism\Raw\Interfaces;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Raw\Objects\Request;

interface CommandInterface
{
    /**
     * CommandInterface constructor.
     * @param Request $request
     */
    public function __construct(
        Request $request,
    );

    /**
     * @return Document
     */
    public function execute(
    ): Document;

    /**
     * @param int|null $serverId
     * @return array
     */
    public function getDefinition(
        ?int $serverId=null,
    ): array;
}