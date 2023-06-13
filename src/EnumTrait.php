<?php

namespace Sbooker\Workflow;

trait EnumTrait
{
    public function equals(mixed $other): bool
    {
        return  $this === $other;
    }

    public function getRawValue(): int|string
    {
        return $this->value;
    }
}