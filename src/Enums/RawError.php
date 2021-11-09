<?php
namespace CarloNicora\Minimalism\Raw\Enums;

enum RawError
{
    case PayloadMissing;
    case UserWithoutCharacter;
    case NotInSession;
    case InSession;

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
        };
    }
}