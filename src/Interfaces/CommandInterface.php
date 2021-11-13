<?php
namespace CarloNicora\Minimalism\Raw\Interfaces;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Raw\Objects\Request;
use CarloNicora\Minimalism\Raw\Raw;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ApplicationCommandInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordInteractionResponseInterface;
use Exception;

interface CommandInterface
{
    /**
     * CommandInterface constructor.
     * @param Request $request
     * @param Raw $raw
     */
    public function __construct(
        Request $request,
        Raw $raw,
    );

    /**
     * @return Document
     */
    public function execute(
    ): Document;

    /**
     * @param int|null $serverId
     * @return ApplicationCommandInterface
     */
    public function getDefinition(
        ?int $serverId=null,
    ): ApplicationCommandInterface;

    /**
     * @param Exception|null $error
     * @param string|null $description
     * @return DiscordInteractionResponseInterface
     */
    public static function generateError(
        ?Exception $error=null,
        ?string $description=null,
    ): DiscordInteractionResponseInterface;
}