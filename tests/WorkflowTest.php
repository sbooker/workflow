<?php

namespace Test\Sbooker\Workflow;

use Ds\Map;
use Ds\Set;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sbooker\Workflow\Status;

final class WorkflowTest extends TestCase
{
    #[DataProvider('transitStatusExamples')]
    public function testSuccessTransit(Status $current, Status $next, Status $wrong, callable $builder): void
    {
        $workflow = new Workflow($current, $builder);

        $this->assertTrue($workflow->isInStatus($current));
        $this->assertTrue($workflow->canTransitTo($next));
        $this->assertFalse($workflow->canTransitTo($wrong));

        $workflow->transitTo($next);

        $this->assertTrue($workflow->isInStatus($next));
        $this->assertTrue($workflow->canTransitTo($wrong));
        $this->assertFalse($workflow->canTransitTo($current));
    }

    public static function transitStatusExamples(): array
    {
        return [
            [ StringStatus::first, StringStatus::second, StringStatus::third, [ self::class, 'stringTransitionMap' ] ],
            [ IntStatus::first, IntStatus::second, IntStatus::third, [ self::class, 'intTransitionMap' ] ],
        ];
    }

    public static function stringTransitionMap(): Map
    {
        $map = new Map();

        $map->put(StringStatus::first, new Set([StringStatus::second]));
        $map->put(StringStatus::second, new Set([StringStatus::third]));

        return $map;
    }

    public static function intTransitionMap(): Map
    {
        $map = new Map();

        $map->put(IntStatus::first, new Set([IntStatus::second]));
        $map->put(IntStatus::second, new Set([IntStatus::third]));

        return $map;
    }
}