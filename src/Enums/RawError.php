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
            self::CampaignNotInitialised => 'The GM has not created the campaign yet!'
        };
    }
}