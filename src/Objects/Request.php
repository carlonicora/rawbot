<?php
namespace CarloNicora\Minimalism\Raw\Objects;

use CarloNicora\Minimalism\Raw\Data\Objects\Character;
use CarloNicora\Minimalism\Raw\Data\Objects\Server;
use CarloNicora\Minimalism\Raw\Services\Discord\Payload\Payload;

class Request
{
    /**
     * @param Payload|null $payload
     * @param Server $server
     * @param bool $isGM
     * @param Character|null $character
     * @param Character|null $nonPlayingCharacter
     */
    public function __construct(
        private ?Payload $payload,
        private Server $server,
        private bool $isGM,
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
     * @param Payload $payload
     */
    public function setPayload(
        Payload $payload,
    ): void
    {
        $this->payload = $payload;
    }

    /**
     * @return Server
     */
    public function getServer(
    ): Server
    {
        return $this->server;
    }

    /**
     * @param Server $server
     */
    public function setServer(
        Server $server,
    ): void
    {
        $this->server = $server;
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