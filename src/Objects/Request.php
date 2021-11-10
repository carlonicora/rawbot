<?php
namespace CarloNicora\Minimalism\Raw\Objects;

use CarloNicora\Minimalism\Raw\Data\Objects\Character;
use CarloNicora\Minimalism\Raw\Data\Objects\Server;
use CarloNicora\Minimalism\Raw\Services\Discord\Payload\Payload;

class Request
{
    /**
     * @param Payload|null $payload
     * @param bool $isGM
     * @param Server|null $server
     * @param Character|null $character
     * @param Character|null $nonPlayingCharacter
     */
    public function __construct(
        private ?Payload $payload=null,
        private ?Server $server=null,
        private bool $isGM=false,
        private ?Character $character=null,
        private ?Character $nonPlayingCharacter=null,
    )
    {
    }

    /**
     * @return Payload|null
     */
    public function getPayload(
    ): ?Payload
    {
        return $this->payload;
    }

    /**
     * @return Server|null
     */
    public function getServer(
    ): ?Server
    {
        return $this->server;
    }

    /**
     * @return Character|null
     */
    public function getCharacter(
    ): ?Character
    {
        return $this->character;
    }

    /**
     * @param Character|null $character
     */
    public function setCharacter(
        ?Character $character,
    ): void
    {
        $this->character = $character;
    }

    /**
     * @return Character|null
     */
    public function getNonPlayingCharacter(
    ): ?Character
    {
        return $this->nonPlayingCharacter;
    }

    /**
     * @param Character|null $nonPlayingCharacter
     */
    public function setNonPlayingCharacter(
        ?Character $nonPlayingCharacter,
    ): void
    {
        $this->nonPlayingCharacter = $nonPlayingCharacter;
    }

    /**
     * @return bool
     */
    public function isGM(
    ): bool
    {
        return $this->isGM;
    }

    /**
     * @param bool $isGM
     */
    public function setIsGM(
        bool $isGM,
    ): void
    {
        $this->isGM = $isGM;
    }
}