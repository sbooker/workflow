<?php

declare(strict_types=1);

namespace Sbooker\Workflow;

use Ds\Map;
use Ds\Set;

abstract class Workflow
{
    /** @var Status  */
    protected $status;

    /** @var \DateTimeImmutable  */
    protected $changedAt;

    /**
     * @var Map<Status, Set<Status>>
     */
    private $transitionMap = null;

    public function __construct(Status $initialStatus)
    {
        $this->setStatus($initialStatus);
    }

    abstract protected function buildTransitionMap(): Map;

    abstract protected function getStatusClass(): string;

    final public function getChangedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }

    final public function getStatus(): Status
    {
        return $this->status;
    }

    final public function isInStatus(Status $status): bool
    {
        return $this->getStatus()->equals($status);
    }

    /**
     * @throws FlowError
     */
    final public function transitTo(Status $status): void
    {
        $statusType = $this->getStatusClass();
        if (!($status instanceof $statusType)) {
            throw new \RuntimeException("Invalid status type '{$status}' for " . get_class($this));
        }

        if (!$this->canTransitTo($status)) {
            throw new FlowError("Can not transit status from {$this->getStatus()->getRawValue()} to {$status->getRawValue()}");
        }
        $this->setStatus($status);
    }

    final protected function setStatus(Status $status)
    {
        $this->status = $status;
        $this->registerChange();
    }

    private function registerChange(): void
    {
        $this->changedAt = new \DateTimeImmutable();
    }

    public function canTransitTo(Status $status): bool
    {
        try {
            /** @var Set<Status> $transitionStatusSet */
            $transitionStatusSet = $this->getTransitionMap()->get($this->getStatus());
            return $transitionStatusSet->contains($status);
        } catch (\OutOfBoundsException $e) {
            return false;
        }
    }

    private function getTransitionMap(): Map
    {
        if (!$this->transitionMap) {
            $this->transitionMap = $this->buildTransitionMap();
        }
        return $this->transitionMap;
    }
}