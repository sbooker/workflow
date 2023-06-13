<?php

namespace Test\Sbooker\Workflow;

use Sbooker\Workflow\EnumTrait;
use Sbooker\Workflow\Status;

enum StringStatus: string implements Status
{
    use EnumTrait;

    case first = "first";
    case second = "second";
    case third = "third";
}