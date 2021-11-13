<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractRawDiscordView;
use CarloNicora\Minimalism\Raw\Enums\CriticalRoll;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Enums\RawDocument;
use CarloNicora\Minimalism\Raw\Services\Discord\Enums\DiscordColour;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedField;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedImage;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedThumbnail;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

class RollDiscordView extends AbstractRawDiscordView
{
    /**
     * @return array
     * @throws Exception
     */
    public function exportView(
    ): array
    {
        if (count($this->document->resources) > 1 || $this->document->resources[0]->type === RawDocument::Dice->value){
            $message = $this->rollDice($this->document->resources);
        } else {
            $message = $this->rollAbility($this->document->resources[0]);
        }

        return $message->export();
    }

    /**
     * @param ResourceObject $roll
     * @return DiscordMessage
     * @throws Exception
     */
    private function rollAbility(
        ResourceObject $roll,
    ): DiscordMessage
    {
        $message = new DiscordMessage();

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


        $url = match(CriticalRoll::from($roll->attributes->get('critical'))) {
            CriticalRoll::Success => 'https://media.giphy.com/media/Z9KdRxSrTcDHGE6Ipf/giphy.gif',
            CriticalRoll::Failure => 'https://vignette.wikia.nocookie.net/kingsway-role-playing-group/images/a/ab/A7c1d56e7cdb84ee25e6769d9c7b9910--tabletop-rpg-tabletop-games.jpg',
            CriticalRoll::None => null,
        };

        $image = null;
        if ($url !== null){
            $image = new DiscordEmbedImage(
                url: $url
            );
        }

        $message->addEmbed(
            new DiscordEmbed(
                title: $ability->attributes->get('name') . (($characterAbility->attributes->get('specialisation') === '/') ? '' : '/' . $characterAbility->attributes->get('specialisation')) . ' check for ' . ($character->attributes->get('name')??$character->attributes->get('shortName')),
                color: $colour,
                footer: $this->createFooter(RawCommand::Ability->value, ($this->document->meta->has('version') ? $this->document->meta->get('version') : null )),
                image: $image,
                thumbnail: new DiscordEmbedThumbnail(
                    $character->attributes->get('thumbnail')
                ),
                fields: [
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

        return $message;
    }

    /**
     * @param ResourceObject[] $rolls
     * @return DiscordMessage
     * @throws Exception
     */
    private function rollDice(
        array $rolls,
    ): DiscordMessage
    {
        $message = new DiscordMessage();

        $textReference = '';
        $textValues = '';

        foreach ($rolls as $diceResource){
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
                footer: $this->createFooter(RawCommand::Dice->value, ($this->document->meta->has('version') ? $this->document->meta->get('version') : null )),
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

        return $message;
    }
}