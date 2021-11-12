<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\Minimalism\Raw\Enums\RawDocument;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractView;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedField;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedImage;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedThumbnail;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

class SessionView extends AbstractView
{
    /**
     * @return array
     * @throws Exception
     */
    public function exportView(
    ): array
    {
        $message = new DiscordMessage();
        $session = $this->document->resources[0];

        if ($session->type === RawDocument::SessionStart->value) {
            $message->addEmbed(
                new DiscordEmbed(
                    title: 'The game is on!',
                    description: 'It\'s time to **stop the chattering** and **get ready to roll**!' . PHP_EOL
                    . 'The session is officially started. You can now `/roll` your abilities when needed!',
                    footer: DiscordMessageFactory::createFooter(
                        type: 'Session management'
                    ),
                    image: DiscordMessageFactory::createImage(
                        url: 'https://media.giphy.com/media/zf8yrM8nVERvW/source.gif'
                    ),
                )
            );
        } else {
            $message->addEmbed(
                new DiscordEmbed(
                    title: 'That\'s a wrap!',
                    description: 'This is the end of tonight\'s session! I hope you enjoyed!',
                    footer: DiscordMessageFactory::createFooter(
                        type: 'Session management'
                    ),
                    image: new DiscordEmbedImage('https://media.giphy.com/media/lD76yTC5zxZPG/giphy.gif'),
                )
            );

            foreach ($session->relationship('characters')->resourceLinkage->resources as $characterResource){
                $characterMessage = 'You have been awarded ' . $characterResource->meta->get('bonus') . ' points' . PHP_EOL
                    . '(_You have ' . $characterResource->meta->get('totalBonus') . ' bonus points_)' . PHP_EOL
                    . ' ' . PHP_EOL;

                $abilityField = '';
                $updateField = '';
                $rollField = '';

                foreach ($characterResource->relationship('abilities')->resourceLinkage->resources ?? [] as $abilityResource) {
                    $rollField .= '_' . $abilityResource->meta->get('roll') . '_' . PHP_EOL;

                    if ($abilityResource->meta->get('updated')) {
                        $abilityField .= '** ' . $abilityResource->attributes->get('name') . '**' . PHP_EOL;
                        $updateField .= '`+' . $abilityResource->meta->get('bonus') . '`' . PHP_EOL;
                    } else {
                        $abilityField .= $abilityResource->attributes->get('name') . PHP_EOL;
                        $updateField .= '_not improved_' . PHP_EOL;
                    }
                }

                $characterEmbed = new DiscordEmbed(
                    title: $characterResource->attributes->get('name'),
                    description: $characterMessage,
                    footer: DiscordMessageFactory::createFooter(
                        type: 'Session management'
                    ),
                    thumbnail: ($characterResource->attributes->get('thumbnail') !== null ? new DiscordEmbedThumbnail($characterResource->attributes->get('thumbnail')) : null),
                );

                if ($abilityField !== '') {
                    $characterEmbed->addField(
                        new DiscordEmbedField(
                            name: 'Ability',
                            value: $abilityField,
                            inline: true,
                        )
                    );
                    $characterEmbed->addField(
                        new DiscordEmbedField(
                            name: 'Upgrade',
                            value: $updateField,
                            inline: true,
                        )
                    );
                    $characterEmbed->addField(
                        new DiscordEmbedField(
                            name: 'Roll',
                            value: $rollField,
                            inline: true,
                        )
                    );
                }


                $message->addEmbed($characterEmbed);
            }
        }

        return $message->export();
    }
}