<?php
namespace CarloNicora\Minimalism\Raw\Enums;

enum RawError
{
    case PayloadMissing;
    case UserWithoutCharacter;
    case NotInSession;
    case InSession;
    case CampaignAlreadyInitialised;
    case CharacterNotSpecified;
    case CampaignNotInitialised;
    case InvalidTraitTraitValue;
    case InvalidThumbnailLink;

    /**
     * @return string
     */
    public function getMessage(
    ): string
    {
        return match ($this) {
            self::PayloadMissing => 'Payload is missing',
            self::UserWithoutCharacter => 'You don\'t have a character yet',
            self::NotInSession => 'You are not in a session',
            self::CampaignAlreadyInitialised => 'You already have a running campaign on this server',
            self::CharacterNotSpecified => 'No character selected',
            self::CampaignNotInitialised => 'The GM has not created the campaign yet!',
            self::InvalidTraitTraitValue => 'The value of the trait is not valid. It should be between 1 and 20',
            self::InvalidThumbnailLink => 'The link to the thumbnail is not valid. It should start with `http` and end in `.jpg` or `.png`',
            self::InSession => 'You are already in a game session',
        };
    }
}