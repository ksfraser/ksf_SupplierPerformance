<?php
/**
 * FrontAccounting Supplier Performance Module - Entities
 *
 * Entity classes for supplier performance tracking.
 *
 * @package FA\Modules\SupplierPerformance
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\SupplierPerformance\Entities;

/**
 * Supplier Evaluation Entity
 *
 * Represents a periodic evaluation of supplier performance.
 */
class SupplierEvaluation
{
    private int $id;
    private string $evaluationReference;
    private int $supplierId;
    private string $evaluationDate;
    private int $evaluatorId;
    private string $evaluationPeriodStart;
    private string $evaluationPeriodEnd;
    private float $overallScore;
    private float $qualityScore;
    private float $deliveryScore;
    private float $priceScore;
    private float $serviceScore;
    private float $complianceScore;
    private string $status;
    private string $comments;
    private string $recommendations;
    private ?string $finalizedAt;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->evaluationReference = $data['evaluation_reference'] ?? '';
        $this->supplierId = $data['supplier_id'] ?? 0;
        $this->evaluationDate = $data['evaluation_date'] ?? '';
        $this->evaluatorId = $data['evaluator_id'] ?? 0;
        $this->evaluationPeriodStart = $data['evaluation_period_start'] ?? '';
        $this->evaluationPeriodEnd = $data['evaluation_period_end'] ?? '';
        $this->overallScore = (float)($data['overall_score'] ?? 0.00);
        $this->qualityScore = (float)($data['quality_score'] ?? 0.00);
        $this->deliveryScore = (float)($data['delivery_score'] ?? 0.00);
        $this->priceScore = (float)($data['price_score'] ?? 0.00);
        $this->serviceScore = (float)($data['service_score'] ?? 0.00);
        $this->complianceScore = (float)($data['compliance_score'] ?? 0.00);
        $this->status = $data['status'] ?? 'draft';
        $this->comments = $data['comments'] ?? '';
        $this->recommendations = $data['recommendations'] ?? '';
        $this->finalizedAt = $data['finalized_at'] ?? null;
        $this->createdAt = $data['created_at'] ?? '';
        $this->updatedAt = $data['updated_at'] ?? '';
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getEvaluationReference(): string { return $this->evaluationReference; }
    public function getSupplierId(): int { return $this->supplierId; }
    public function getEvaluationDate(): string { return $this->evaluationDate; }
    public function getEvaluatorId(): int { return $this->evaluatorId; }
    public function getEvaluationPeriodStart(): string { return $this->evaluationPeriodStart; }
    public function getEvaluationPeriodEnd(): string { return $this->evaluationPeriodEnd; }
    public function getOverallScore(): float { return $this->overallScore; }
    public function getQualityScore(): float { return $this->qualityScore; }
    public function getDeliveryScore(): float { return $this->deliveryScore; }
    public function getPriceScore(): float { return $this->priceScore; }
    public function getServiceScore(): float { return $this->serviceScore; }
    public function getComplianceScore(): float { return $this->complianceScore; }
    public function getStatus(): string { return $this->status; }
    public function getComments(): string { return $this->comments; }
    public function getRecommendations(): string { return $this->recommendations; }
    public function getFinalizedAt(): ?string { return $this->finalizedAt; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    // Business logic methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    public function getRating(): string
    {
        if ($this->overallScore >= 90) return 'Excellent';
        if ($this->overallScore >= 80) return 'Good';
        if ($this->overallScore >= 70) return 'Satisfactory';
        if ($this->overallScore >= 60) return 'Needs Improvement';
        return 'Poor';
    }

    public function getWeakestArea(): string
    {
        $scores = [
            'Quality' => $this->qualityScore,
            'Delivery' => $this->deliveryScore,
            'Price' => $this->priceScore,
            'Service' => $this->serviceScore,
            'Compliance' => $this->complianceScore
        ];

        return array_search(min($scores), $scores);
    }

    public function getStrongestArea(): string
    {
        $scores = [
            'Quality' => $this->qualityScore,
            'Delivery' => $this->deliveryScore,
            'Price' => $this->priceScore,
            'Service' => $this->serviceScore,
            'Compliance' => $this->complianceScore
        ];

        return array_search(max($scores), $scores);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'evaluation_reference' => $this->evaluationReference,
            'supplier_id' => $this->supplierId,
            'evaluation_date' => $this->evaluationDate,
            'evaluator_id' => $this->evaluatorId,
            'evaluation_period_start' => $this->evaluationPeriodStart,
            'evaluation_period_end' => $this->evaluationPeriodEnd,
            'overall_score' => $this->overallScore,
            'quality_score' => $this->qualityScore,
            'delivery_score' => $this->deliveryScore,
            'price_score' => $this->priceScore,
            'service_score' => $this->serviceScore,
            'compliance_score' => $this->complianceScore,
            'status' => $this->status,
            'comments' => $this->comments,
            'recommendations' => $this->recommendations,
            'finalized_at' => $this->finalizedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}

/**
 * Supplier Metric Entity
 *
 * Represents a tracked metric for supplier performance.
 */
class SupplierMetric
{
    private int $id;
    private int $supplierId;
    private string $metricType;
    private string $metricDate;
    private float $metricValue;
    private ?float $targetValue;
    private string $unit;
    private string $period;
    private string $notes;
    private ?int $recordedBy;
    private string $createdAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->supplierId = $data['supplier_id'] ?? 0;
        $this->metricType = $data['metric_type'] ?? '';
        $this->metricDate = $data['metric_date'] ?? '';
        $this->metricValue = (float)($data['metric_value'] ?? 0.00);
        $this->targetValue = isset($data['target_value']) ? (float)$data['target_value'] : null;
        $this->unit = $data['unit'] ?? '';
        $this->period = $data['period'] ?? 'monthly';
        $this->notes = $data['notes'] ?? '';
        $this->recordedBy = $data['recorded_by'] ?? null;
        $this->createdAt = $data['created_at'] ?? '';
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getSupplierId(): int { return $this->supplierId; }
    public function getMetricType(): string { return $this->metricType; }
    public function getMetricDate(): string { return $this->metricDate; }
    public function getMetricValue(): float { return $this->metricValue; }
    public function getTargetValue(): ?float { return $this->targetValue; }
    public function getUnit(): string { return $this->unit; }
    public function getPeriod(): string { return $this->period; }
    public function getNotes(): string { return $this->notes; }
    public function getRecordedBy(): ?int { return $this->recordedBy; }
    public function getCreatedAt(): string { return $this->createdAt; }

    // Business logic methods
    public function meetsTarget(): ?bool
    {
        if ($this->targetValue === null) {
            return null;
        }

        // For most metrics, higher is better
        // Specific metric types may need different logic
        return $this->metricValue >= $this->targetValue;
    }

    public function getPerformancePercentage(): ?float
    {
        if ($this->targetValue === null || $this->targetValue == 0) {
            return null;
        }

        return round(($this->metricValue / $this->targetValue) * 100, 2);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplierId,
            'metric_type' => $this->metricType,
            'metric_date' => $this->metricDate,
            'metric_value' => $this->metricValue,
            'target_value' => $this->targetValue,
            'unit' => $this->unit,
            'period' => $this->period,
            'notes' => $this->notes,
            'recorded_by' => $this->recordedBy,
            'created_at' => $this->createdAt
        ];
    }
}

/**
 * Supplier Rating Entity
 *
 * Represents the current rating and score for a supplier.
 */
class SupplierRating
{
    private int $id;
    private int $supplierId;
    private float $currentScore;
    private string $rating;
    private string $ratingDate;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->supplierId = $data['supplier_id'] ?? 0;
        $this->currentScore = (float)($data['current_score'] ?? 0.00);
        $this->rating = $data['rating'] ?? 'not_rated';
        $this->ratingDate = $data['rating_date'] ?? '';
        $this->createdAt = $data['created_at'] ?? '';
        $this->updatedAt = $data['updated_at'] ?? '';
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getSupplierId(): int { return $this->supplierId; }
    public function getCurrentScore(): float { return $this->currentScore; }
    public function getRating(): string { return $this->rating; }
    public function getRatingDate(): string { return $this->ratingDate; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    // Business logic methods
    public function isExcellent(): bool
    {
        return $this->rating === 'excellent';
    }

    public function isGood(): bool
    {
        return $this->rating === 'good';
    }

    public function needsImprovement(): bool
    {
        return in_array($this->rating, ['needs_improvement', 'poor']);
    }

    public function getRatingLabel(): string
    {
        $labels = [
            'excellent' => 'Excellent (90+)',
            'good' => 'Good (80-89)',
            'satisfactory' => 'Satisfactory (70-79)',
            'needs_improvement' => 'Needs Improvement (60-69)',
            'poor' => 'Poor (<60)',
            'not_rated' => 'Not Rated'
        ];

        return $labels[$this->rating] ?? 'Unknown';
    }

    public function getRatingColor(): string
    {
        $colors = [
            'excellent' => 'green',
            'good' => 'lightgreen',
            'satisfactory' => 'yellow',
            'needs_improvement' => 'orange',
            'poor' => 'red',
            'not_rated' => 'gray'
        ];

        return $colors[$this->rating] ?? 'gray';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplierId,
            'current_score' => $this->currentScore,
            'rating' => $this->rating,
            'rating_date' => $this->ratingDate,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}
