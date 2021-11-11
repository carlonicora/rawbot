<?php
namespace CarloNicora\Minimalism\Raw\Enums;

enum CriticalRoll: int
{
    case None=0;
    case Success=1;
    case Failure=2;
}