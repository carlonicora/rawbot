<?php
namespace CarloNicora\Minimalism\Raw\Models\Discord;

use CarloNicora\Minimalism\Raw\Abstracts\AbstractDiscordModel;
use CarloNicora\Minimalism\Raw\Commands\RollCommand;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use Exception;

class Roll extends AbstractDiscordModel
{
    /**
     * @param array|null $payload
     * @return int
     * @throws Exception
     */
    public function post(
        ?array $payload
    ): int
    {
        try {
            $request = $this->generateRequest($payload);

            $this->document = (new RollCommand(
                request: $request,
            ))->execute();
        } catch (Exception $e) {
            $this->document = DiscordMessageFactory::generateErrorDocument(description: $e->getMessage());
        }


        return 200;
    }
}