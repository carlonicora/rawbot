<?php
namespace CarloNicora\Minimalism\Raw\Models\Discord;

use CarloNicora\Minimalism\Raw\Abstracts\AbstractDiscordModel;
use CarloNicora\Minimalism\Raw\Commands\CampaignCommand;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Views\CampaignView;
use Exception;

class Campaign extends AbstractDiscordModel
{
    /** @var string|null  */
    protected ?string $view=CampaignView::class;

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

            $command = new CampaignCommand(
                request: $request,
            );

            $this->document = $command->execute();
        } catch (Exception $e) {
            $this->document = DiscordMessageFactory::generateErrorDocument(description: $e->getMessage());
        }

        return 200;
    }
}