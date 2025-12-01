<?php
/**
 * FrontAccounting Supplier Performance Module - Events
 *
 * Event classes for supplier performance system integration.
 *
 * @package FA\Modules\SupplierPerformance
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\SupplierPerformance\Events;

use FA\Modules\SupplierPerformance\Entities\SupplierEvaluation;
use FA\Modules\SupplierPerformance\Entities\SupplierMetric;
use FA\Modules\SupplierPerformance\Entities\SupplierRating;

/**
 * Supplier Evaluation Created Event
 */
class SupplierEvaluationCreatedEvent
{
    private SupplierEvaluation $evaluation;
    private \DateTimeImmutable $occurredAt;

    public function __construct(SupplierEvaluation $evaluation)
    {
        $this->evaluation = $evaluation;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getEvaluation(): SupplierEvaluation
    {
        return $this->evaluation;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getSupplierId(): int
    {
        return $this->evaluation->getSupplierId();
    }
}

/**
 * Supplier Evaluation Finalized Event
 */
class SupplierEvaluationFinalizedEvent
{
    private SupplierEvaluation $evaluation;
    private \DateTimeImmutable $occurredAt;

    public function __construct(SupplierEvaluation $evaluation)
    {
        $this->evaluation = $evaluation;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getEvaluation(): SupplierEvaluation
    {
        return $this->evaluation;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getSupplierId(): int
    {
        return $this->evaluation->getSupplierId();
    }

    public function getOverallScore(): float
    {
        return $this->evaluation->getOverallScore();
    }
}

/**
 * Supplier Metric Tracked Event
 */
class SupplierMetricTrackedEvent
{
    private SupplierMetric $metric;
    private \DateTimeImmutable $occurredAt;

    public function __construct(SupplierMetric $metric)
    {
        $this->metric = $metric;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getMetric(): SupplierMetric
    {
        return $this->metric;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getSupplierId(): int
    {
        return $this->metric->getSupplierId();
    }

    public function getMetricType(): string
    {
        return $this->metric->getMetricType();
    }

    public function getMetricValue(): float
    {
        return $this->metric->getMetricValue();
    }
}

/**
 * Supplier Rating Updated Event
 */
class SupplierRatingUpdatedEvent
{
    private SupplierRating $rating;
    private \DateTimeImmutable $occurredAt;

    public function __construct(SupplierRating $rating)
    {
        $this->rating = $rating;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getRating(): SupplierRating
    {
        return $this->rating;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getSupplierId(): int
    {
        return $this->rating->getSupplierId();
    }

    public function getCurrentScore(): float
    {
        return $this->rating->getCurrentScore();
    }

    public function getRatingLevel(): string
    {
        return $this->rating->getRating();
    }
}

/**
 * Supplier Performance Alert Event
 * 
 * Triggered when supplier performance falls below threshold
 */
class SupplierPerformanceAlertEvent
{
    private int $supplierId;
    private string $alertType;
    private string $message;
    private array $data;
    private \DateTimeImmutable $occurredAt;

    public function __construct(int $supplierId, string $alertType, string $message, array $data = [])
    {
        $this->supplierId = $supplierId;
        $this->alertType = $alertType;
        $this->message = $message;
        $this->data = $data;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getSupplierId(): int
    {
        return $this->supplierId;
    }

    public function getAlertType(): string
    {
        return $this->alertType;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
