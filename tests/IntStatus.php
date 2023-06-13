<?php

namespace Test\Sbooker\Workflow;

use Sbooker\Workflow\EnumTrait;
use Sbooker\Workflow\Status;

enum IntStatus: int implements Status
{
    use EnumTrait;

    case first = 1;
    case second = 2;
    case third = 3;
}
