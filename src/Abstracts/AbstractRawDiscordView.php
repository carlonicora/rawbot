<?php
namespace CarloNicora\Minimalism\Raw\Abstracts;

use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractDiscordView;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordEmbedFooterInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedFooter;

abstract class AbstractRawDiscordView extends AbstractDiscordView
{
    /**
     * @param string $type
     * @param string|null $version
     * @return DiscordEmbedFooterInterface
     */
    public function createFooter(
        string $type,
        ?string $version=null,
    ): DiscordEmbedFooterInterface
    {
        return new DiscordEmbedFooter(
            text: ucfirst($type) . ($version !== null ? ' - RAW Bot v' . $version : ''),
            icon_url: 'https://previews.123rf.com/images/martialred/martialred1512/martialred151200052/49796805-20-sided-20d-dice-line-art-icon-for-apps-and-websites.jpg'
        );
    }
}