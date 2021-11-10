<?php
namespace CarloNicora\Minimalism\Raw\Models\Discord;

use CarloNicora\Minimalism\Raw\Abstracts\AbstractDiscordModel;
use CarloNicora\Minimalism\Raw\Commands\DiceCommand;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Views\DiceView;
use Exception;

class Dice extends AbstractDiscordModel
{
    /** @var string|null  */
    protected ?string $view=DiceView::class;

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

            $this->document = (new DiceCommand(
                request: $request,
            ))->execute();
        } catch (Exception $e) {
            $this->document = DiscordMessageFactory::generateErrorDocument(description: $e->getMessage());
        }


        return 200;
    }
}