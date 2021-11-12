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
    case List='list';
    case Set='set';
    case Update='update';
    case Detail='detail';
    case PlayingCharacter='pc';
    case Value='value';
    case Ability='ability';
    case Specialisation='specialisation';
    case Up='up';
    case Roll='roll';
    case Assign='assign';
    case Start='start';
    case End='end';
    case Type='type';
}