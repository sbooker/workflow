<?php

namespace Test\Sbooker\Workflow;

use Ds\Map;
use Ds\Set;
use Sbooker\Workflow\Status;
use Sbooker\Workflow\Workflow as BaseWorkflow;

/**
 * @template T of Status
 */
class Workflow extends BaseWorkflow
{
    /**
     * @var callable(): Map<T, Set<T>
     */
    private $transitionMapBuilder;

    /**
     * @var class-string<T>
     */
    private string $statusClass;

    public function __construct(Status $initialStatus, callable $builder)
    {
        parent::__construct($initialStatus);
        $this->statusClass = get_class($initialStatus);
        $this->transitionMapBuilder = $builder;
    }

    protected function buildTransitionMap(): Map
    {
        $builder = $this->transitionMapBuilder;
        return $builder();
    }

    protected function getStatusClass(): string
    {
        return $this->statusClass;
    }
}