<?php
namespace CarloNicora\Minimalism\Raw\Models;

use CarloNicora\Minimalism\Raw\Abstracts\AbstractRawModel;
use CarloNicora\Minimalism\Raw\Commands\CampaignCommand;
use CarloNicora\Minimalism\Raw\Commands\CharacterCommand;
use CarloNicora\Minimalism\Raw\Commands\DiceCommand;
use CarloNicora\Minimalism\Raw\Commands\RollCommand;
use CarloNicora\Minimalism\Raw\Commands\SessionCommand;
use CarloNicora\Minimalism\Raw\Objects\Request;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class Setup extends AbstractRawModel
{
    /**
     * @return int
     * @throws Exception
     */
    public function cli(): int
    {
        $token = $this->getToken();

        //$currentCommands = $this->getPublishedCommands(token: $token);

        $emptyRequest = new Request();
        $commands = [
            new CharacterCommand($emptyRequest),
            new CampaignCommand($emptyRequest),
            new DiceCommand($emptyRequest),
            new RollCommand($emptyRequest),
            new SessionCommand($emptyRequest),
        ];

        foreach ($commands as $command) {
            $this->addCommand(
                token: $token,
                definition: $command->getDefinition(),
            );
        }

        return 200;
    }

    /**
     * @param string $token
     * @param array $definition
     * @throws Exception
     */
    private function addCommand(
        string $token,
        array $definition,
    ): void
    {
        $client = new Client(['base_uri' => $this->raw->getCommandEndpoint()]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        try {
            $apiResponse = $client->request(
                method: 'POST',
                options: [
                    'headers' => $headers,
                    'body' => json_encode($definition, JSON_THROW_ON_ERROR)
                ]
            );

            json_decode($apiResponse->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            throw new RuntimeException($e->getMessage(), 500);
        }
    }

    /**
     * @param string $token
     * @return array
     * @throws Exception
     */
    private function getPublishedCommands(
        string $token,
    ): array
    {
        $client = new Client(['base_uri' => $this->raw->getCommandEndpoint()]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        try {
            $apiResponse = $client->request(method: 'GET',
                options: [
                    'headers' => $headers
                ]
            );

            return json_decode($apiResponse->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            throw new RuntimeException($e->getMessage(), 500);
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getToken(): string
    {
        $client = new Client(['base_uri' => $this->raw->getApiEndpoint() . '/oauth2/token']);
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $data = [
            'grant_type' => 'client_credentials',
            'scope' => 'applications.commands.update',
        ];

        try {
            $response = $client->request(
                method: 'POST',
                options: [
                    'auth' => ['829691798632792124', '083hOu7L3DsZHPgxTq2LgN0BLA_VvubA'],
                    'headers' => $headers,
                    'form_params' => $data,
                ]
            );

            $body = $response->getBody();
            $contents = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);

            return $contents['access_token'];
        } catch (GuzzleException $e) {
            throw new RuntimeException('Error retrieving the token: ' . $e->getMessage(), 401);
        }
    }
}