<?php
namespace CarloNicora\Minimalism\Raw\Enums;

enum RawDocument: string
{
    case SessionStart='sessionStart';
    case SessionEnd='sessionEnd';
    case Dice='dice';
    case Character='character';
    case Ability='ability';
}