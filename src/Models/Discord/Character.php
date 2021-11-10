<?php
namespace CarloNicora\Minimalism\Raw\Models\Discord;

use CarloNicora\Minimalism\Raw\Abstracts\AbstractDiscordModel;
use CarloNicora\Minimalism\Raw\Commands\CharacterCommand;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Views\CharacterView;
use Exception;

class Character extends AbstractDiscordModel
{
    /** @var string|null  */
    protected ?string $view=CharacterView::class;

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

            $command = new CharacterCommand(
                request: $request,
            );

            $this->document = $command->execute();
        } catch (Exception $e) {
            $this->document = DiscordMessageFactory::generateErrorDocument(description: $e->getMessage());
        }

        return 200;
    }
}