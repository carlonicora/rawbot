<?php
namespace CarloNicora\Minimalism\Raw\Factories;

use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Data\DataReaders\AbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Services\Discord\ApplicationCommands\ApplicationCommandChoice;
use CarloNicora\Minimalism\Raw\Services\Discord\ApplicationCommands\ApplicationCommandOption;
use CarloNicora\Minimalism\Raw\Services\Discord\Enums\ApplicationCommandOptionType;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ApplicationCommandChoiceInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ApplicationCommandOptionInterface;
use Exception;

class CommandOptionsFactory
{
    /**
     * @return ApplicationCommandChoiceInterface[]
     * @throws Exception
     */
    private static function getAbilityChoices(
    ): array
    {
        /** @var AbilitiesDataReader $readAbility */
        $readAbility = MinimalismObjectsFactory::create(AbilitiesDataReader::class);
        $abilities = $readAbility->all();

        $response = [];
        foreach ($abilities ?? [] as $ability){
            $response[] = new ApplicationCommandChoice(
                name: $ability->getFullName() . ' (' . $ability->getTrait()->value . ')',
                value: $ability->getId(),
            );
        }

        return $response;
    }

    /**
     * @return ApplicationCommandOptionInterface
     * @throws Exception
     */
    public static function getAbilityListSubOption(
    ): ApplicationCommandOptionInterface
    {
        return new ApplicationCommandOption(
            type: ApplicationCommandOptionType::INTEGER,
            name:PayloadParameter::Ability->value,
            description: 'The ability to set',
            isRequired: true,
            choices: self::getAbilityChoices(),
        );
    }

    /**
     * @return ApplicationCommandOptionInterface
     *
     */
    public static function getAbilitySpecialisationSubOption(
    ): ApplicationCommandOptionInterface
    {
        return new ApplicationCommandOption(
            type: ApplicationCommandOptionType::STRING,
            name:PayloadParameter::Specialisation->value,
            description: 'The ability specialisation (if any)',
        );
    }

    /**
     * @param bool $isRequired
     * @return ApplicationCommandOptionInterface
     */
    public static function getCharacterSelectionSubOption(
        bool $isRequired=false,
    ): ApplicationCommandOptionInterface
    {
        return new ApplicationCommandOption(
            type: ApplicationCommandOptionType::STRING,
            name:PayloadParameter::Character->value,
            description: '[GM Only] The non player character identifier',
            isRequired: $isRequired,
        );
    }

    /**
     * @param string $description
     * @param bool $isRequired
     * @return ApplicationCommandOptionInterface
     */
    public static function getValueSubOption(
        string $description,
        bool $isRequired=true,
    ): ApplicationCommandOptionInterface
    {
        return new ApplicationCommandOption(
            type: ApplicationCommandOptionType::INTEGER,
            name:PayloadParameter::Value->value,
            description: $description,
            isRequired: $isRequired,
        );
    }

    /**
     * @param string $description
     * @param bool $isRequired
     * @return ApplicationCommandOptionInterface
     */
    public static function getNameSubOption(
        string $description,
        bool $isRequired=true,
    ): ApplicationCommandOptionInterface
    {
        return new ApplicationCommandOption(
            type: ApplicationCommandOptionType::STRING,
            name:PayloadParameter::Name->value,
            description: $description,
            isRequired: $isRequired,
        );
    }

    /**
     * @return ApplicationCommandOptionInterface
     */
    public static function getBonusSubOption(
    ): ApplicationCommandOptionInterface
    {
        return new ApplicationCommandOption(
            type: ApplicationCommandOptionType::INTEGER,
            name: RawCommand::Bonus->value,
            description: 'Advantage or disadvantage to the roll',
        );
    }

    /**
     * @return ApplicationCommandOptionInterface
     * @throws Exception
     */
    public static function getBonusRollOption(
    ): ApplicationCommandOptionInterface
    {
        $response = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::SUB_COMMAND,
            name:PayloadParameter::Roll->value,
            description: 'Roll a bonus on an ability to try and upgrade it',
        );

        $response->addOption(self::getAbilityListSubOption());
        $response->addOption(self::getAbilitySpecialisationSubOption());
        $response->addOption(self::getCharacterSelectionSubOption());

        return $response;
    }

    /**
     * @return ApplicationCommandOptionInterface
     * @throws Exception
     */
    public static function getBonusUpOtion(
    ): ApplicationCommandOptionInterface
    {
        $response = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::SUB_COMMAND,
            name:PayloadParameter::Up->value,
            description: 'The ability to upgrade by 1 point',
        );

        $response->addOption(self::getAbilityListSubOption());
        $response->addOption(self::getAbilitySpecialisationSubOption());
        $response->addOption(self::getCharacterSelectionSubOption());

        return $response;
    }

    /**
     * @return ApplicationCommandOptionInterface
     */
    public static function getBonusAssignCommand(
    ): ApplicationCommandOptionInterface
    {
        $response = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::SUB_COMMAND,
            name: PayloadParameter::Assign->value,
            description: 'Define the bonus point assignment',
        );

        $response->addOption(self::getCharacterSelectionSubOption(true));
        $response->addOption(self::getValueSubOption('The amount of bonus points to assign'));

        return $response;
    }

    /**
     * @return ApplicationCommandOptionInterface
     */
    public static function getCharacterDetailsCommand(
    ): ApplicationCommandOptionInterface
    {
        $response = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::SUB_COMMAND,
            name: PayloadParameter::Detail->value,
            description: 'Get the details of your character or of an NPC',
        );

        $response->addOption(self::getCharacterSelectionSubOption());

        return $response;
    }

    /**
     * @return ApplicationCommandOptionInterface
     */
    public static function getCharacterCreationCommand(
    ): ApplicationCommandOptionInterface
    {
        $response = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::SUB_COMMAND,
            name: PayloadParameter::Create->value,
            description: 'Create a new character',
        );

        $response->addOption(self::getNameSubOption('An identifier for the player (your first name) or the non-player character'));

        return $response;
    }

    /**
     * @return ApplicationCommandOptionInterface
     */
    public static function getCharacterVariablesCommand(
    ): ApplicationCommandOptionInterface
    {
        $response = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::SUB_COMMAND,
            name: PayloadParameter::Set->value,
            description: 'Set the details of your character',
        );

        $response->addOption(self::getNameSubOption('Change the name of your character', false));

        $response->addOption(
            new ApplicationCommandOption(
                type: ApplicationCommandOptionType::STRING,
                name: PayloadParameter::Description->value,
                description: 'Change the description of your character',
            )
        );

        $response->addOption(
            new ApplicationCommandOption(
                type: ApplicationCommandOptionType::STRING,
                name: PayloadParameter::Thumbnail->value,
                description: 'Change the thumbnail of your character. You need to use a valid URL to an image (NOT A WEB PAGE)',
            )
        );

        $bodyOption = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::INTEGER,
            name: PayloadParameter::Body->value,
            description: 'Change the body trait value of your character (between 1 and 20)',
        );
        $bodyOption->setMinValue(1);
        $bodyOption->setMaxValue(20);
        $response->addOption($bodyOption);

        $mindOption = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::INTEGER,
            name: PayloadParameter::Mind->value,
            description: 'Change the mind trait value of your character (between 1 and 20)',
        );
        $mindOption->setMinValue(1);
        $mindOption->setMaxValue(20);
        $response->addOption($mindOption);

        $spiritOption = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::INTEGER,
            name: PayloadParameter::Spirit->value,
            description: 'Change the spirit trait value of your character (between 1 and 20)',
        );
        $spiritOption->setMinValue(1);
        $spiritOption->setMaxValue(20);
        $response->addOption($spiritOption);

        $response->addOption(self::getCharacterSelectionSubOption());

        return $response;
    }

    /**
     * @return ApplicationCommandOptionInterface
     */
    public static function getRollDiceCommand(
    ): ApplicationCommandOptionInterface
    {
        $response = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::SUB_COMMAND,
            name: PayloadParameter::Dice->value,
            description: 'Roll a dice',
        );

        $response->addOption(
            new ApplicationCommandOption(
                type: ApplicationCommandOptionType::STRING,
                name: RawCommand::Dice->value,
                description: 'Specify the number of dices and the sides (1d20, 3d6, ...)',
                isRequired: true,
            )
        );

        $response->addOption(self::getBonusSubOption());

        return $response;
    }

    /**
     * @return ApplicationCommandOptionInterface
     * @throws Exception
     */
    public static function getRollAbilityCommand(
    ): ApplicationCommandOptionInterface
    {
        $response = new ApplicationCommandOption(
            type: ApplicationCommandOptionType::SUB_COMMAND,
            name: PayloadParameter::Ability->value,
            description: 'The name of the ability you want to use',
        );

        $response->addOption(self::getAbilityListSubOption());
        $response->addOption(self::getAbilitySpecialisationSubOption());
        $response->addOption(self::getBonusSubOption());
        $response->addOption(self::getCharacterSelectionSubOption());

        return $response;
    }
}