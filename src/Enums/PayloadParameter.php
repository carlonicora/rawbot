<?php
namespace CarloNicora\Minimalism\Raw\Enums;

enum PayloadParameter: string
{
    case Character='npc';
    case Command='command';
    case Dice='dice';
    case Bonus='bonus';
}