<?php
namespace CarloNicora\Minimalism\Raw\Views;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Raw\Enums\CriticalRoll;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\Abstracts\AbstractView;
use CarloNicora\Minimalism\Raw\Services\Discord\Enums\DiscordColour;
use CarloNicora\Minimalism\Raw\Services\Discord\Enums\DiscordFlag;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedField;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedImage;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedThumbnail;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

class BonusView extends AbstractView
{
    /** @var ResourceObject  */
    private ResourceObject $bonus;

    /** @var ResourceObject  */
    private ResourceObject $character;

    /** @var ResourceObject|null  */
    private ?ResourceObject $characterAbility=null;

    /** @var ResourceObject|null  */
    private ?ResourceObject $ability=null;

    /**
     * @return array
     * @throws Exception
     */
    public function exportView(): array
    {
        $message = new DiscordMessage();

        $this->bonus = $this->document->resources[0];
        $this->character = $this->bonus->relationship('character')->resourceLinkage->resources[0];
        if (array_key_exists('characterAbility', $this->bonus->relationships)) {
            $this->characterAbility = $this->bonus->relationship('characterAbility')->resourceLinkage->resources[0];
        }
        if (array_key_exists('ability', $this->bonus->relationships)) {
            $this->ability = $this->bonus->relationship('ability')->resourceLinkage->resources[0];
        }

        switch ($this->bonus->attributes->get('type')) {
            case 'assign':
                $this->getAssignView($message);
                break;
            case 'up':
                $this->getUpView($message);
                break;
            case 'roll':
                $this->getRollView($message);
                break;
        }

        return $message->export();
    }

    /**
     * @param DiscordMessage $message
     * @throws Exception
     */
    public function getAssignView(
        DiscordMessage $message,
    ): void
    {
        if ($this->character->attributes->get('userId') !== null){
            $description = '<@' . $this->character->attributes->get('userId') . '>,' . PHP_EOL
            . 'your character, **' . $this->character->attributes->get('name') . '**, has been awarded **' . $this->bonus->attributes->get('value') . '** additional bonus points!';
        } else {
            $description = '**' . $this->character->attributes->get('name') . '** has been awarded **' . $this->bonus->attributes->get('value') . '** additional bonus points!';
            $message->addFlag(DiscordFlag::EPHEMERAL);
        }

        $message->addEmbed(
            new DiscordEmbed(
                title: 'Bonus Assignment',
                description: $description,
                color: DiscordColour::Blue->value,
                footer: DiscordMessageFactory::createFooter(
                    type: 'Bonus management'
                ),
                image: new DiscordEmbedImage('https://media.giphy.com/media/mlTGpQTEnbHPy/giphy.gif'),
                thumbnail: new DiscordEmbedThumbnail($this->character->attributes->get('thumbnail')),
            )
        );
    }

    /**
     * @param DiscordMessage $message
     * @throws Exception
     */
    public function getUpView(
        DiscordMessage $message,
    ): void
    {
        $message->addEmbed(
            new DiscordEmbed(
                title: 'Bonus Up!',
                description: '<@' . $this->character->attributes->get('userId') . '>,' . PHP_EOL
                . 'you have updated ' . $this->character->attributes->get('name') . '\'s ' . $this->ability->attributes->get('fullName') . ($this->characterAbility->attributes->get('specialisation')==='/' ? '' : '/' . $this->characterAbility->attributes->get('specialisation')) . ' ability by **one** point.',
                color: DiscordColour::Blue->value,
                footer: DiscordMessageFactory::createFooter(
                    type: 'Bonus management'
                ),
                image: new DiscordEmbedImage('https://media.giphy.com/media/qUDenOaWmXImQ/giphy.gif'),
                thumbnail: new DiscordEmbedThumbnail($this->character->attributes->get('thumbnail')),
            )
        );
    }

    /**
     * @param DiscordMessage $message
     * @throws Exception
     */
    public function getRollView(
        DiscordMessage $message,
    ): void
    {
        $indexTable = 'Roll' . PHP_EOL .
            'Ability Value' . PHP_EOL .
            'TraitValue' . PHP_EOL .
            '___' . PHP_EOL .
            '**Bonus Points**';
        $resultTable = (($this->bonus->attributes->get('critical')===CriticalRoll::Success->value) ? '**' : '') .
            $this->bonus->attributes->get('roll') .
            (($this->bonus->attributes->get('critical')===CriticalRoll::Success->value) ? '**' : '') .
            PHP_EOL .
            $this->ability->attributes->get('value') . PHP_EOL .
            $this->character->attributes->get($this->ability->attributes->get('trait')) . PHP_EOL .
            '___' . PHP_EOL .
            '**' . $this->bonus->attributes->get('bonus') . '**';
        $description = '<@' . $this->character->attributes->get('userId') . '>,' . PHP_EOL;

        if ($this->bonus->attributes->get('bonus') === 0){
            $title = 'Bonus roll failed';
            $color = DiscordColour::Red->value;
            $image = 'https://media.giphy.com/media/d2W7eZX5z62ziqdi/giphy.gif';
            $description .= 'you failed to upgrade ' . $this->character->attributes->get('name') . '\'s ' . $this->ability->attributes->get('fullName') . ($this->characterAbility->attributes->get('specialisation')==='/' ? '' : '/' . $this->characterAbility->attributes->get('specialisation')) . ' ability value!';
        } else {
            $title = 'Bonus roll succeeded';
            $color = DiscordColour::Green->value;
            $image = 'https://media.giphy.com/media/qUDenOaWmXImQ/giphy.gif';
            $description .= 'you upgraded ' . $this->character->attributes->get('name') . '\'s ' . $this->ability->attributes->get('fullName') . ($this->characterAbility->attributes->get('specialisation')==='/' ? '' : '/' . $this->characterAbility->attributes->get('specialisation')) . ' ability value by **' . $this->bonus->attributes->get('bonus') . '** points!';
        }

        $fields = [
            new DiscordEmbedField(
                name: 'Stats',
                value: $indexTable,
                inline: true,
            ),
            new DiscordEmbedField(
                name: 'Values',
                value: $resultTable,
                inline: true,
            )
        ];

        $message->addEmbed(
            new DiscordEmbed(
                title: $title,
                description: $description,
                color: $color,
                footer: DiscordMessageFactory::createFooter(
                    type: 'Bonus management'
                ),
                image: new DiscordEmbedImage($image),
                thumbnail: new DiscordEmbedThumbnail($this->character->attributes->get('thumbnail')),
                fields: $fields,
            )
        );
    }
}