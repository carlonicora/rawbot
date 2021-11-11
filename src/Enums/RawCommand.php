<?php
namespace CarloNicora\Minimalism\Raw\Enums;

enum RawCommand: string
{
    case Character='character';
    case Ability='ability';
    case Roll='roll';
    case Dice='dice';
    case Campaign='campaign';
    case Session='session';
}