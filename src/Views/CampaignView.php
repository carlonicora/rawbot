<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractView;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

class CampaignView extends AbstractView
{
    /**
     * @return array
     * @throws Exception
     */
    public function exportView(): array
    {
        $message = new DiscordMessage();

        return $message->export();
    }
}