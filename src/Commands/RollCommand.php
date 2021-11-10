<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataReaders\AbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataReaders\CharacterAbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Data\DataWriters\CharacterAbilitiesDataWriter;
use CarloNicora\Minimalism\Raw\Enums\CriticalRoll;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Helpers\DiceRoller;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordEmbedInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordInteractionResponseInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedField;
use Exception;

class RollCommand extends AbstractCommand
{
    /**
     * @return Document
     * @throws Exception
     */
    public function execute(
    ): Document
    {
        /*
        if (!$this->server['inSession']){
            return DiscordMessageFactory::generateError(description: 'You can only roll your abilities during a game session.');
        }

        if ($this->character['isNPC'] === 1 && !$this->isGM){
            return DiscordMessageFactory::generateError(description: 'Only the GM can control the NPCs!');
        }

        $threshold = $this->getParameterValue('threshold');
        $bonus = $this->getParameterValue('bonus');
        $abilityName = $this->getParameterValue('ability');
        $challengedCharacter = $this->getParameterValue('challenge');

        $abilitySpecialisation = '/';
        if (str_contains($abilityName, '/')) {
            [$abilityName, $abilitySpecialisation] = explode('/', $abilityName);
        }

        $abilityFullName = $abilityName . ($abilitySpecialisation === '/' ? '' : $abilitySpecialisation);

        $readAbilities = MinimalismObjectsFactory::create(AbilitiesDataReader::class);

        $ability = $readAbilities->byName(
            name: $abilityName
        );

        $readCharacterAbilities = MinimalismObjectsFactory::create(CharacterAbilitiesDataReader::class);

        try {
            $characterAbility = $readCharacterAbilities->byCharacterIdAbilityIdSpecialisation(
                characterId: $this->character['characterId'],
                abilityId: $ability['abilityId'],
                specialisation: $abilitySpecialisation,
            );
        } catch (Exception) {
            $characterAbility = [
                'characterId' => $this->character['characterId'],
                'abilityId' => $ability['abilityId'],
                'specialisation' => $abilitySpecialisation,
                'value' => 0,
                'used' => 0,
                'wasUpdated' => 0
            ];
        }

        if ($characterAbility['used'] !== 1) {
            $writeCharacterAbilities = MinimalismObjectsFactory::create(CharacterAbilitiesDataWriter::class);
            $characterAbility['used'] = 1;

            $writeCharacterAbilities->update($characterAbility);
        }

        $critical = CriticalRoll::None;
        $roll = DiceRoller::roll(20, $critical);

        $total = $characterAbility['value'] + $this->character[$ability['trait']] + $roll;

        $textReference = 'Ability' . PHP_EOL . ucfirst($ability['trait']) . PHP_EOL . 'Dice Roll' . PHP_EOL;
        $textValues = $characterAbility['value'] . PHP_EOL . $this->character[$ability['trait']] . PHP_EOL . $roll . PHP_EOL;

        if ($critical === CriticalRoll::Success) {
            $textReference .= '**Critical Success**' . PHP_EOL;
            $textValues .= '+20' . PHP_EOL;
            $total += 20;
        } elseif ($critical === CriticalRoll::Failure) {
            $textReference .= '**Critical Failure**' . PHP_EOL;
            $textValues .= '-20' . PHP_EOL;
            $total -= 21;
        }

        if ($bonus !== null) {
            $textReference .= 'Bonus' . PHP_EOL;
            $textValues .= $bonus . PHP_EOL;
            $total += (int)$bonus;
        }

        if ($characterAbility['value'] === 0) {
            $textReference .= 'Untrained disadvantage' . PHP_EOL;
            $textValues .= '-10' . PHP_EOL;
            $total -= 10;
        }

        $successes = 0;

        if ($threshold !== null){
            if ($total >= $threshold){
                $successes = (int)(($total - $threshold) / 25) + 1;
            } else {
                $successes = (int)(($total - $threshold) / 25) - 1;
            }
        } elseif ($total > 0) {
            $successes = (int)($total/25);
        }

        if ($successes > 0){
            $colour = DiscordEmbedInterface::COLOUR_GREEN;
        } else {
            $colour = DiscordEmbedInterface::COLOUR_RED;
        }

        $textReference .= ' ' . PHP_EOL . '**Total**';
        $textValues .= ' ' . PHP_EOL . '**' . $total . '**';

        if ($threshold !== null){
            $textReference .= ' ' . PHP_EOL . '**Required**';
            $textValues .= ' ' . PHP_EOL . '**' . $threshold . '**';
        }

        $textReference .= ' ' . PHP_EOL . $successes . ' successes';

        $this->message->addEmbed(
            new DiscordEmbed(
                title: $abilityFullName . ' check for ' . $this->character['name'],
                color: $colour,
                footer: DiscordMessageFactory::createFooter(
                    type: 'Ability check'
                ),
                image: DiscordMessageFactory::createRollImage(
                    type: $critical
                ),
                thumbnail: DiscordMessageFactory::createCharacterThumbnail(
                    character: $this->character
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

        return $this->message;
        */

        return $this->response;
    }

    /**
     * @param int|null $serverId
     * @return array
     */
    public function getDefinition(
        ?int $serverId=null,
    ): array
    {
        return [
            'name' => 'roll',
            'description' => 'roll an ability or a dice',
            'options' => [
                [
                    'type' => 3,
                    'name' => 'ability',
                    'description' => 'The name of the ability you want to use.',
                    'required' => true,
                    'choices' => [
                        [
                            'name' => 'Empathy',
                            'value' => 'empathy'
                        ],
                        [
                            'name' => 'Fast Talk',
                            'value' => 'fasttalk'
                        ],
                        [
                            'name' => 'Willpower',
                            'value' => 'willpower'
                        ],
                        [
                            'name' => 'Hand to Hand Combat',
                            'value' => 'handtohand'
                        ],
                    ]
                ],
                [
                    'type' => 3,
                    'name' => 'npc',
                    'description' => 'Short name of the Non Player Character',
                    'required' => false,
                    'choices' => [
                        [
                            'name' => 'Elsa Wittlesberg',
                            'value' => 'elsa',
                        ],
                        [
                            'name' => 'Hercule',
                            'value' => 'hercule',
                        ],
                        [
                            'name' => 'Matteo Morelli',
                            'value' => 'morelli',
                        ]
                    ]
                ],[
                    'type' => 3,
                    'name' => 'challenge',
                    'description' => 'Short name of the Character to challenge',
                    'required' => false,
                    'choices' => [
                        [
                            'name' => 'Elsa Wittlesberg',
                            'value' => 'elsa',
                        ],
                        [
                            'name' => 'Hercule',
                            'value' => 'hercule',
                        ],
                        [
                            'name' => 'Matteo Morelli',
                            'value' => 'morelli',
                        ],
                    ]
                ],
                [
                    'type' => 4,
                    'name' => 'bonus',
                    'description' => 'Advantage or disadvantage to the roll',
                    'required' => false,
                ],
                [
                    'type' => 4,
                    'name' => 'threshold',
                    'description' => 'Value required for the action to succeed',
                    'required' => false,
                ],
            ],
        ];
    }
}