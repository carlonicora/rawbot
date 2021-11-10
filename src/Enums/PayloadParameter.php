<?php
namespace CarloNicora\Minimalism\Raw\Enums;

enum PayloadParameter: string
{
    case Character='npc';
    case Command='command';
    case Dice='dice';
    case Bonus='bonus';
    case Name='name';
    case Body='body';
    case Mind='mind';
    case Spirit='spirit';
    case Description='description';
    case Thumbnail='thumbnail';
    case Create='create';
}