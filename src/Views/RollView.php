<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\Minimalism\Raw\Enums\CriticalRoll;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractView;
use CarloNicora\Minimalism\Raw\Services\Discord\Enums\DiscordColour;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedField;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedThumbnail;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

class RollView extends AbstractView
{
    /**
     * @return array
     * @throws Exception
     */
    public function exportView(
    ): array
    {
        $message = new DiscordMessage();

        $roll = $this->document->resources[0];
        $ability = $roll->relationship('ability')->resourceLinkage->resources[0];
        $characterAbility = $roll->relationship('characterAbility')->resourceLinkage->resources[0];
        $character = $roll->relationship('character')->resourceLinkage->resources[0];

        $textReference = 'Ability' . PHP_EOL . ucfirst($ability->attributes->get('trait')) . PHP_EOL . 'Dice Roll' . PHP_EOL;
        $textValues = $characterAbility->attributes->get('value') . PHP_EOL . $character->attributes->get($ability->attributes->get('trait')) . PHP_EOL . $roll->attributes->get('roll') . PHP_EOL;

        if ($roll->attributes->get('critical') === CriticalRoll::Success->value) {
            $textReference .= '**Critical Success**' . PHP_EOL;
            $textValues .= '+20' . PHP_EOL;
        } elseif ($roll->attributes->get('critical') === CriticalRoll::Success->value) {
            $textReference .= '**Critical Failure**' . PHP_EOL;
            $textValues .= '-20' . PHP_EOL;
        }

        if ($roll->attributes->has('bonus')) {
            $textReference .= 'Bonus' . PHP_EOL;
            $textValues .= $roll->attributes->get('bonus') . PHP_EOL;
        }

        if ($characterAbility->attributes->get('value') === 0) {
            $textReference .= 'Untrained disadvantage' . PHP_EOL;
            $textValues .= '-10' . PHP_EOL;
        }

        $colour = match ($roll->attributes->get('successes')) {
            0 => DiscordColour::Red->value,
            1 => DiscordColour::Grey->value,
            2 => DiscordColour::Blue->value,
            default => DiscordColour::Green->value,
        };

        $textReference .= ' ' . PHP_EOL . '**Total**';
        $textValues .= ' ' . PHP_EOL . '**' . $roll->attributes->get('total') . '**';

        $textReference .= ' ' . PHP_EOL . $roll->attributes->get('successes') . ' successes';

        $message->addEmbed(
            new DiscordEmbed(
                title: $ability->attributes->get('name') . (($characterAbility->attributes->get('specialisation') === '/') ? '' : '/' . $characterAbility->attributes->get('specialisation')) . ' check for ' . ($character->attributes->get('name')??$character->attributes->get('shortName')),
                color: $colour,
                footer: DiscordMessageFactory::createFooter(
                    type: 'Ability check'
                ),
                image: DiscordMessageFactory::createRollImage(
                    type: CriticalRoll::from($roll->attributes->get('critical'))
                ),
                thumbnail: new DiscordEmbedThumbnail(
                    $character->attributes->get('thumbnail')
                ), fields: [
                new DiscordEmbedField(
                    name: 'Reference',
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