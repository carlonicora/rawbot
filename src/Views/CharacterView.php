<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Raw\Enums\RawTrait;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractView;
use CarloNicora\Minimalism\Raw\Services\Discord\Enums\DiscordColour;
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
        if ($this->document->meta->has('list')){
            $message = $this->buildList(
                characters: $this->document->resources,
            );
        } else {
            $message = $this->buildSingle(
                character: $this->document->resources[0],
            );
        }

        return $message->export();
    }

    /**
     * @param ResourceObject[] $characters
     * @return DiscordMessage
     * @throws Exception
     */
    private function buildList(
        array $characters,
    ): DiscordMessage
    {
        $message = new DiscordMessage();

        $embeds = [];

        foreach ($characters ?? [] as $character) {
            $color = DiscordColour::Grey->value;
            if (!$character->attributes->get('isNPC')){
                if ($character->attributes->get('isMe')){
                    $color = DiscordColour::Red->value;
                } else {
                    $color = DiscordColour::Blue->value;
                }
            }

            $embeds[] = new DiscordEmbed(
                title: ($character->attributes->get('isNPC') ? '[NPC]' : '') . $character->attributes->get('name') . ' (_' . $character->attributes->get('shortName') . '_)',
                description: $character->attributes->get('description')??'',
                color: $color,
                footer: DiscordMessageFactory::createFooter(
                    type: 'Character management'
                ),
                thumbnail: ($character->attributes->get('thumbnail') !== null ? new DiscordEmbedThumbnail($character->attributes->get('thumbnail')) : null),
            );
        }

        $message->addFlag(DiscordFlag::EPHEMERAL);

        $message->setEmbeds($embeds);

        return $message;
    }

    /**
     * @param ResourceObject $character
     * @return DiscordMessage
     * @throws Exception
     */
    private function buildSingle(
        ResourceObject $character,
    ): DiscordMessage
    {
        $message = new DiscordMessage();

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

        if ($character->attributes->get('bonus') > 0) {
            $updateableAbilitiesDescription = 'You haven\'t updated any ability just yet, so you can\'t use `/bonus up` on any of your abilities';
            if (array_key_exists('updatedAbilities', $character->relationships)) {
                $updateableAbilitiesDescription = 'You have updated the following abilities (for which you can use `/bonus up` to increase their values by 1 point by using one of your bonuses:' . PHP_EOL;

                    foreach ($character->relationship('updatedAbilities')->resourceLinkage->resources ?? [] as $updateableAbility){
                        $updateableAbilitiesDescription .= ' * ' . $updateableAbility->attributes->get('name') . PHP_EOL;
                    }
            }

            $fields[] = new DiscordEmbedField(
                name: 'Updateable Abilities',
                value: $updateableAbilitiesDescription,
                inline: false
            );
        }

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

        return $message;
    }
}