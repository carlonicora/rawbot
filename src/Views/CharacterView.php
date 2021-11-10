<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\Minimalism\Raw\Enums\RawTrait;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractView;
use CarloNicora\Minimalism\Raw\Services\Discord\Enums\DiscordFlag;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedField;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedThumbnail;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

class CharacterView extends AbstractView
{
    /**
     * @return array
     * @throws Exception
     */
    public function exportView(
    ): array
    {
        $message = new DiscordMessage();

        $character = $this->document->resources[0];

        $fields = [];

        foreach (RawTrait::cases() as $trait){
            $abilities = $character->relationship($trait->value)->resourceLinkage->resources;
            $text = '________' . PHP_EOL;

            foreach ($abilities as $ability) {
                if ($ability->attributes->get('value') !== null && $ability->attributes->get('value') > 0){
                    $abilityText = '**' . $ability->attributes->get('name') .  ' (' . $ability->attributes->get('value') . ")**";
                } else {
                    $abilityText = $ability->attributes->get('name');
                }
                if ($ability->attributes->get('hasBeenUsed')){
                    $abilityText .= '_*_';
                }
                $text .= $abilityText . PHP_EOL;
            }

            $fields[] = new DiscordEmbedField(
                name: ucfirst($trait->value) . ' ' . $character->attributes->get($trait->value),
                value: $text,
                inline: true,
            );
        }

        $fields[] = new DiscordEmbedField(
            name: 'Additional stats',
            value: 'Bonus points available: ' . $character->attributes->get('bonus') . PHP_EOL
                . 'Damages: ' . $character->attributes->get('bonus') . ' (_' . $character->attributes->get('lifePoints') . ' life points available_)',
            inline: false
        );

        $description = $character->attributes->get('description')??'';
        if ($character->meta->get('updated') === true){
            $description .= PHP_EOL . '___' . PHP_EOL . ' _Your character has been successfully updated_ ' . PHP_EOL . '___' . PHP_EOL;
        }

        $message->addEmbed(
            new DiscordEmbed(
                title: $character->attributes->get('name'),
                description: $description,
                footer: DiscordMessageFactory::createFooter(
                    type: 'Character management'
                ),
                thumbnail: ($character->attributes->get('thumbnail') !== null ? new DiscordEmbedThumbnail($character->attributes->get('thumbnail')) : null),
                fields: $fields,
            )
        );

        $message->addFlag(DiscordFlag::EPHEMERAL);

        return $message->export();
    }
}