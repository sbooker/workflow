<?php

declare(strict_types=1);

namespace Sbooker\Workflow;

interface Status
{
    public function equals(self $other): bool;

    public function getRawValue(): int | string;
}