<?php
namespace CarloNicora\Minimalism\Raw\Models;

use CarloNicora\Minimalism\Raw\Abstracts\AbstractRawModel;
use CarloNicora\Minimalism\Raw\Commands\AbilityCommand;
use CarloNicora\Minimalism\Raw\Commands\BonusCommand;
use CarloNicora\Minimalism\Raw\Commands\CampaignCommand;
use CarloNicora\Minimalism\Raw\Commands\CharacterCommand;
use CarloNicora\Minimalism\Raw\Commands\InitiativeCommand;
use CarloNicora\Minimalism\Raw\Commands\RollCommand;
use CarloNicora\Minimalism\Raw\Commands\SessionCommand;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Objects\Request;
use CarloNicora\Minimalism\Raw\Raw;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ApplicationCommandInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class Setup extends AbstractRawModel
{
    /**
     * @param Raw $raw
     * @return int
     * @throws Exception
     */
    public function cli(
        Raw $raw,
    ): int
    {
        $token = $this->getToken();

        $currentCommands = $this->getPublishedCommands(token: $token);

        foreach ($currentCommands ?? [] as $command){
            if (!in_array($command['name'], [
                RawCommand::Ability->value,
                RawCommand::Roll->value,
                RawCommand::Character->value,
                RawCommand::Campaign->value,
                RawCommand::Session->value,
                RawCommand::Bonus->value,
                RawCommand::Initiative->value,
            ], true)
            ) {
                $this->deleteCommand(
                    token: $token,
                    commandId: $command['id'],
                );
            }
        }

        $emptyRequest = new Request();
        $commands = [
            //new AbilityCommand($emptyRequest, $raw),
            //new BonusCommand($emptyRequest, $raw),
            //new CampaignCommand($emptyRequest, $raw),
            //new CharacterCommand($emptyRequest, $raw),
            //new InitiativeCommand($emptyRequest, $raw),
            //new RollCommand($emptyRequest, $raw),
            //new SessionCommand($emptyRequest, $raw),
        ];

        foreach ($commands as $command) {
            $this->addCommand(
                token: $token,
                definition: $command->getDefinition(),
            );
        }

        return 200;
    }

    private function deleteCommand(
        string $token,
        string $commandId,
    ): void
    {
        $client = new Client(['base_uri' => $this->raw->getCommandEndpoint() . '/' . $commandId]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        try {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $apiResponse = $client->request(
                method: 'DELETE',
                options: [
                    'headers' => $headers,
                ]
            );
        } catch (GuzzleException $e) {
            throw new RuntimeException($e->getMessage(), 500);
        }
    }

    /**
     * @param string $token
     * @param ApplicationCommandInterface $definition
     * @throws Exception
     */
    private function addCommand(
        string $token,
        ApplicationCommandInterface $definition,
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
                    'body' => json_encode($definition->export(), JSON_THROW_ON_ERROR)
                ]
            );

            $remaining = (int)$apiResponse->getHeader('X-RateLimit-Remaining');
            if ($remaining === 0){
                $waitFor = (int)$apiResponse->getHeader('X-RateLimit-Reset-After');
                sleep($waitFor+1);
            }
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