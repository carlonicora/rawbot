<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractView;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedField;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

class DiceView extends AbstractView
{
    /**
     * @return array
     * @throws Exception
     */
    public function exportView(): array
    {
        $message = new DiscordMessage();

        $textReference = '';
        $textValues = '';

        foreach ($this->document->resources as $diceResource){
            $textReference .= $diceResource->attributes->get('type') . PHP_EOL;
            $textValues .= $diceResource->attributes->get('roll') . PHP_EOL;
        }

        if ($this->document->meta->has('bonus')){
            $textReference .= 'Bonus ' . PHP_EOL;
            $textValues .= $this->document->meta->get('bonus') . PHP_EOL;
        }

        $textReference .= ' ' . PHP_EOL . '**Total**';
        $textValues .= ' ' . PHP_EOL . '**' . $this->document->meta->get('result') . '**';

        $message->addEmbed(
            new DiscordEmbed(
                title: $this->document->meta->get('dice') . ' Dice Roll',
                footer: DiscordMessageFactory::createFooter(
                    type: 'Dice Roll'
                ),
                fields: [
                    new DiscordEmbedField(
                        name: 'Dice',
                        value: $textReference,
                        inline: true,
                    ),
                    new DiscordEmbedField(
                        name: 'Value',
                        value: $textValues,
                        inline: true,
                    ),
                ]
            )
        );

        return $message->export();
    }
}