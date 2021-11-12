<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractView;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
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

        $message->addEmbed(
            new DiscordEmbed(
                title: 'Your campaign is ready to go!',
                description: 'Your campaign `' . $this->document->resources[0]->attributes->get('name') . '` is ready. You can now invite your players or just planning your non player characters!',
                footer: DiscordMessageFactory::createFooter(
                    type: 'Campaign'
                ),
                image: DiscordMessageFactory::createImage('https://media.giphy.com/media/SXNvIUIDidBdOq9ndX/giphy.gif'),
            )
        );

        return $message->export();
    }
}