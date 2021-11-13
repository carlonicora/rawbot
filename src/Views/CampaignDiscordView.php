<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\Minimalism\Raw\Abstracts\AbstractRawDiscordView;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedImage;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

class CampaignDiscordView extends AbstractRawDiscordView
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
                footer: $this->createFooter(RawCommand::Campaign->value, ($this->document->meta->has('version') ? $this->document->meta->get('version') : null )),
                image: new DiscordEmbedImage('https://media.giphy.com/media/SXNvIUIDidBdOq9ndX/giphy.gif'),
            )
        );

        return $message->export();
    }
}