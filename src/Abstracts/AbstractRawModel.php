<?php
namespace CarloNicora\Minimalism\Raw\Abstracts;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Factories\ServiceFactory;
use CarloNicora\Minimalism\Raw\Raw;
use Discord\Interaction;
use Exception;

abstract class AbstractRawModel extends AbstractModel
{
    /** @var Raw|null  */
    protected Raw|null $raw;

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
}