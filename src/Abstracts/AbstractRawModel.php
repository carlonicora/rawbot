<?php
namespace CarloNicora\Minimalism\Raw\Abstracts;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Factories\ServiceFactory;
use CarloNicora\Minimalism\Interfaces\MinimalismObjectInterface;
use CarloNicora\Minimalism\Raw\Commands\CharacterCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharactersDataReader;
use CarloNicora\Minimalism\Raw\Data\DataReaders\ServersDataReader;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Exceptions\ErrorException;
use CarloNicora\Minimalism\Raw\Objects\Request;
use CarloNicora\Minimalism\Raw\Raw;
use CarloNicora\Minimalism\Raw\Services\Discord\Payload\Payload;
use Discord\Interaction;
use Exception;
use RuntimeException;

abstract class AbstractRawModel extends AbstractModel
{
    /** @var Raw|null  */
    protected Raw|null $raw;

    /** @var string|null  */
    protected ?string $command=null;

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

        $this->raw = $services->create(Raw::class);

        $this->readCharacter = MinimalismObjectsFactory::create(CharactersDataReader::class);
        $this->readServer = MinimalismObjectsFactory::create(ServersDataReader::class);
    }

    /**
     * @throws Exception
     */
    public function validate(
        ?array $payload,
    ): void
    {
        $signature = $_SERVER['HTTP_X_SIGNATURE_ED25519'];
        $timestamp = $_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'];
        $postData = file_get_contents('php://input');

        if (!Interaction::verifyKey($postData, $signature, $timestamp, $this->raw->getPublicKey())) {
            header('HTTP/1.1 401 Unauthorized');
            http_response_code(401);

            echo "Not verified";
            exit;
        }

        if ($payload['type'] === 1) {
            $response = [
                'type' => 1
            ];

            header('Content-Type: application/json');
            header('HTTP/1.1 200 OK');
            http_response_code(200);
            echo (json_encode($response, JSON_THROW_ON_ERROR));
            exit;
        }
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
                    if ($this->command !== CharacterCommand::class || !$payloadObject->hasParameter(PayloadParameter::Create)) {
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

    /**
     * @param Exception $exception
     * @return Document
     * @throws Exception
     */
    public function returnError(
        Exception $exception,
    ): Document
    {
        $response = new Document();

        $error = new ResourceObject(
            type: 'error',
        );

        $error->attributes->add(name: 'description', value: $exception->getMessage());
        $error->attributes->add(name: 'image', value: 'https://media.giphy.com/media/USNlL9p2fxY6Q/source.gif');

        $response->addResource(
            resource: $error,
        );

        return $response;
    }
}