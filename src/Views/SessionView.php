<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\Minimalism\Raw\Enums\RawDocument;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractView;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedField;
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
            $fields = [];

            foreach ($session->relationship('characters')->resourceLinkage->resources as $characterResource){
                $characterMessage = 'You have been awarded ' . $characterResource->meta->get('bonus') . ' points' . PHP_EOL
                    . '(_You have ' . $characterResource->meta->get('totalBonus') . ' bonus points_)' . PHP_EOL
                    . ' ' . PHP_EOL;

                foreach ($characterResource->relationship('abilities')->resourceLinkage->resources as $abilityResource){
                    if ($abilityResource->meta->get('updated')){
                        $characterMessage .= '**+** ' . $abilityResource->attributes->get('name')
                            . ' (' . $abilityResource->attributes->get('value')  . ') `+' . $abilityResource->meta->get('bonus') . '` (roll: '. $abilityResource->meta->get('roll') .')'
                            . PHP_EOL;
                    } else {
                        $characterMessage .= '- ' . $abilityResource->attributes->get('name')
                            . ' (' . $abilityResource->attributes->get('value') . ') not improved (roll: ' . $abilityResource->meta->get('roll') . ')'
                            . PHP_EOL;

                    }
                }

                $characterMessage .= '_ _' . PHP_EOL;

                $fields[] = new DiscordEmbedField(
                    name: $characterResource->attributes->get('name'),
                    value: $characterMessage,
                );
            }

            $message->addEmbed(
                new DiscordEmbed(
                    title: 'That\'s a wrap!',
                    description: 'Session end',
                    footer: DiscordMessageFactory::createFooter(
                        type: 'Session management'
                    ),
                    fields: $fields,
                )
            );
        }

        return $message->export();
    }
}