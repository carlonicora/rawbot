<?php
namespace CarloNicora\Minimalism\Raw\Abstracts;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Factories\ServiceFactory;
use CarloNicora\Minimalism\Interfaces\MinimalismObjectInterface;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharactersDataReader;
use CarloNicora\Minimalism\Raw\Data\DataReaders\ServersDataReader;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Exceptions\ErrorException;
use CarloNicora\Minimalism\Raw\Models\Discord\Character;
use CarloNicora\Minimalism\Raw\Objects\Request;
use CarloNicora\Minimalism\Raw\Services\Discord\Payload\Payload;
use Exception;
use RuntimeException;

class AbstractDiscordModel extends AbstractModel
{
    /** @var CharactersDataReader|MinimalismObjectInterface */
    protected CharactersDataReader|MinimalismObjectInterface $readCharacter;

    /** @var ServersDataReader|MinimalismObjectInterface  */
    protected ServersDataReader|MinimalismObjectInterface $readServer;

    /**
     * AbstractRawModel constructor.
     * @param ServiceFactory $services
     * @param array $modelDefinition
     * @param string|null $function
     * @throws Exception
     */
    public function __construct(
        ServiceFactory $services,
        array $modelDefinition,
        ?string $function = null
    )
    {
        parent::__construct(
            services: $services,
            modelDefinition: $modelDefinition,
            function: $function
        );

        $this->readCharacter = MinimalismObjectsFactory::create(CharactersDataReader::class);
        $this->readServer = MinimalismObjectsFactory::create(ServersDataReader::class);
    }

    /**
     * @param array|null $payload
     * @return Request
     * @throws Exception
     */
    protected function generateRequest(
        ?array $payload
    ): Request
    {
        if ($payload === null || $payload === []){
            throw new RuntimeException(RawError::PayloadMissing->getMessage());
        }

        $payloadObject = new Payload($payload);

        try {
            $server = $this->readServer->byDiscordServerId(discordServerId: $payloadObject->getGuild()->getId());
        } catch (Exception) {
            $server = null;
        }

        $isGM = false;
        $character = null;
        $npc = null;

        if ($server !== null) {
            if ($server->getGm() === $payloadObject->getUser()->getId()) {
                $isGM = true;
            } else {
                try {
                    $character = $this->readCharacter->byServerIdDiscordUserId(
                        serverId: $server->getId(),
                        discordUserId: $payloadObject->getUser()->getId(),
                    );
                } catch (Exception) {
                    if (static::class !== Character::class || !$payloadObject->hasParameter(PayloadParameter::Create)) {
                        throw new ErrorException(RawError::UserWithoutCharacter->getMessage());
                    }
                }
            }

            $characterShortName = null;
            if ($payloadObject->hasParameter(PayloadParameter::Character)) {
                $characterShortName = $payloadObject->getParameter(PayloadParameter::Character);
            }

            if (!$isGM && $characterShortName !== null){
                throw new RuntimeException('Only the GM can manage non player characters!');
            }

            if ($isGM && $characterShortName !== null) {
                if ($character === null) {
                    $character = $this->readCharacter->byServerIdShortname(
                        serverId: $server->getId(),
                        shortname: $characterShortName,
                    );
                } else {
                    $npc = $this->readCharacter->byServerIdShortname(
                        serverId: $server->getId(),
                        shortname: $characterShortName,
                    );
                }
            }
        }

        return new Request(
            payload: $payloadObject,
            server: $server,
            isGM: $isGM,
            character: $character,
            nonPlayingCharacter: $npc,
        );
    }
}