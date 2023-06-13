<?php

declare(strict_types=1);

namespace Sbooker\Workflow;

use Ds\Map;
use Ds\Set;

/**
 * @template T of Status
 */
abstract class Workflow
{
    protected Status $status;

    protected \DateTimeImmutable $changedAt;

    /**
     * @var Map<T, Set<T>>
     */
    private ?Map $transitionMap = null;

    public function __construct(Status $initialStatus)
    {
        $this->setStatus($initialStatus);
    }

    /**
     * @return Map<T, Set<T>>
     */
    abstract protected function buildTransitionMap(): Map;

    /**
     * @return class-string<T>
     */
    abstract protected function getStatusClass(): string;

    final public function getChangedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }

    /**
     * @return T
     */
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
            throw new \RuntimeException("Invalid status type '{$status->getRawValue()}' for " . get_class($this));
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
            $transitionStatusSet = $this->getTransitionMap()->get($this->getStatus());
            return $transitionStatusSet->contains($status);
        } catch (\OutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * @return Map<T, Set<T>>
     */
    private function getTransitionMap(): Map
    {
        if (!$this->transitionMap) {
            $this->transitionMap = $this->buildTransitionMap();
        }
        return $this->transitionMap;
    }
}