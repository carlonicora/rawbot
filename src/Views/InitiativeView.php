<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractView;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedField;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedImage;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedThumbnail;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

class InitiativeView extends AbstractView
{
    /**
     * @return array
     * @throws Exception
     */
    public function exportView(
    ): array
    {
        $message = new DiscordMessage();

        $initiativeWinner = '';
        $initiativeWinnerThumbnail = null;
        $initiativeEspositionOrder = '';
        $initiativeValueEspositionOrder = '';
        $initiativeActionOrder = '';

        foreach ($this->document->resources ?? [] as $character){
            $characterAbility = $character->relationship('initiative')->resourceLinkage->resources[0];

            $nameOnly = ($character->attributes->get('name')??$character->attributes->get('shortname'));
            $name = $nameOnly . ' (<@' . $character->attributes->get('userId') . '>)';
            $initiativeEspositionOrder .= ' * ' . $nameOnly . PHP_EOL;
            $initiativeValueEspositionOrder .= $characterAbility->attributes->get('initiative') . ' (_on ' . $characterAbility->attributes->get('ability') . '_)' . PHP_EOL;
            $initiativeActionOrder = ' * ' . $name . PHP_EOL . $initiativeActionOrder;

            $initiativeWinner = $nameOnly;
            $initiativeWinnerThumbnail = $character->attributes->get('thumbnail');
        }

        $fields = [];

        $fields[] = new DiscordEmbedField(
            name: 'Actions declaration',
            value: 'Actions should be declared in this order',
            inline: false,
        );
        $fields[] = new DiscordEmbedField(
            name: 'Character',
            value: $initiativeEspositionOrder,
            inline: true,
        );
        $fields[] = new DiscordEmbedField(
            name: 'Value',
            value: $initiativeValueEspositionOrder,
            inline: true,
        );
        $fields[] = new DiscordEmbedField(
            name: 'Actions execution',
            value: 'Actions should be carried out in the opposite order' . PHP_EOL . ' ' . PHP_EOL
                . $initiativeActionOrder,
            inline: false,
        );

        $initiativeDescription = 'The initiative has been won by **' . $initiativeWinner . '**';
        /*
            . PHP_EOL . ' ' . PHP_EOL
            . 'Actions should be declared in this order:' . PHP_EOL . ' ' . PHP_EOL
            . $initiativeEspositionOrder . ' ' . PHP_EOL
            . '**AND** carried out in the opposite order:' . PHP_EOL . ' ' . PHP_EOL
            . $initiativeActionOrder;
        */

        $message->addEmbed(
            new DiscordEmbed(
                title: 'Initiative',
                description: $initiativeDescription,
                footer: DiscordMessageFactory::createFooter(
                    type: 'Initiative'
                ),
                image: new DiscordEmbedImage('https://media.giphy.com/media/10qKGDzg9kDcXu/giphy-downsized-large.gif'),
                thumbnail: (($initiativeWinnerThumbnail !== null) ? new DiscordEmbedThumbnail($initiativeWinnerThumbnail) : null),
                fields: $fields,
            )
        );

        return $message->export();
    }
}