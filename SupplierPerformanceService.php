<?php
/**
 * FrontAccounting Supplier Performance Module - Service Layer
 *
 * Comprehensive supplier evaluation and performance tracking service.
 *
 * @package FA\Modules\SupplierPerformance
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\SupplierPerformance;

use FA\Database\DBALInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use FA\Modules\SupplierPerformance\Entities\SupplierEvaluation;
use FA\Modules\SupplierPerformance\Entities\SupplierMetric;
use FA\Modules\SupplierPerformance\Entities\SupplierRating;
use FA\Modules\SupplierPerformance\Events\SupplierEvaluationCreatedEvent;
use FA\Modules\SupplierPerformance\Events\SupplierRatingUpdatedEvent;
use FA\Modules\SupplierPerformance\SupplierPerformanceException;
use FA\Modules\SupplierPerformance\SupplierPerformanceValidationException;
use FA\Modules\SupplierPerformance\SupplierEvaluationNotFoundException;

/**
 * Supplier Performance Service
 *
 * Manages supplier evaluation, metrics tracking, and performance ratings.
 */
class SupplierPerformanceService
{
    private DBALInterface $dbal;
    private EventDispatcherInterface $eventDispatcher;
    private LoggerInterface $logger;

    public function __construct(
        DBALInterface $dbal,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->dbal = $dbal;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * Create a supplier evaluation
     *
     * @param array $data Evaluation data
     * @return SupplierEvaluation
     */
    public function createEvaluation(array $data): SupplierEvaluation
    {
        $this->validateEvaluationData($data);

        $evaluationData = [
            'evaluation_reference' => $this->generateEvaluationReference(),
            'supplier_id' => $data['supplier_id'],
            'evaluation_date' => $data['evaluation_date'] ?? date('Y-m-d'),
            'evaluator_id' => $data['evaluator_id'],
            'evaluation_period_start' => $data['period_start'],
            'evaluation_period_end' => $data['period_end'],
            'overall_score' => 0.00, // Will be calculated
            'quality_score' => $data['quality_score'] ?? 0.00,
            'delivery_score' => $data['delivery_score'] ?? 0.00,
            'price_score' => $data['price_score'] ?? 0.00,
            'service_score' => $data['service_score'] ?? 0.00,
            'compliance_score' => $data['compliance_score'] ?? 0.00,
            'status' => 'draft',
            'comments' => $data['comments'] ?? '',
            'recommendations' => $data['recommendations'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Calculate overall score
        $evaluationData['overall_score'] = $this->calculateOverallScore([
            'quality' => $evaluationData['quality_score'],
            'delivery' => $evaluationData['delivery_score'],
            'price' => $evaluationData['price_score'],
            'service' => $evaluationData['service_score'],
            'compliance' => $evaluationData['compliance_score']
        ]);

        $evaluationId = $this->dbal->insert('supplier_evaluations', $evaluationData);

        $evaluation = new SupplierEvaluation(array_merge($evaluationData, ['id' => $evaluationId]));

        $this->logger->info('Supplier evaluation created', [
            'evaluation_id' => $evaluationId,
            'supplier_id' => $evaluation->getSupplierId(),
            'overall_score' => $evaluation->getOverallScore()
        ]);

        $this->eventDispatcher->dispatch(new SupplierEvaluationCreatedEvent($evaluation));

        return $evaluation;
    }

    /**
     * Finalize an evaluation
     *
     * @param int $evaluationId
     * @return SupplierEvaluation
     */
    public function finalizeEvaluation(int $evaluationId): SupplierEvaluation
    {
        $evaluation = $this->getEvaluation($evaluationId);

        if ($evaluation->getStatus() !== 'draft') {
            throw new SupplierPerformanceException(
                "Cannot finalize evaluation with status '{$evaluation->getStatus()}'",
                $evaluationId
            );
        }

        $updateData = [
            'status' => 'finalized',
            'finalized_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->dbal->update('supplier_evaluations', $updateData, ['id' => $evaluationId]);

        // Update supplier's current rating based on this evaluation
        $this->updateSupplierRating($evaluation->getSupplierId(), $evaluation->getOverallScore());

        $evaluation = $this->getEvaluation($evaluationId);

        $this->logger->info('Supplier evaluation finalized', [
            'evaluation_id' => $evaluationId,
            'supplier_id' => $evaluation->getSupplierId()
        ]);

        return $evaluation;
    }

    /**
     * Track supplier metric
     *
     * @param array $data Metric data
     * @return SupplierMetric
     */
    public function trackMetric(array $data): SupplierMetric
    {
        $this->validateMetricData($data);

        $metricData = [
            'supplier_id' => $data['supplier_id'],
            'metric_type' => $data['metric_type'],
            'metric_date' => $data['metric_date'] ?? date('Y-m-d'),
            'metric_value' => $data['metric_value'],
            'target_value' => $data['target_value'] ?? null,
            'unit' => $data['unit'] ?? '',
            'period' => $data['period'] ?? 'monthly',
            'notes' => $data['notes'] ?? '',
            'recorded_by' => $data['recorded_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $metricId = $this->dbal->insert('supplier_metrics', $metricData);

        $metric = new SupplierMetric(array_merge($metricData, ['id' => $metricId]));

        $this->logger->info('Supplier metric tracked', [
            'metric_id' => $metricId,
            'supplier_id' => $metric->getSupplierId(),
            'metric_type' => $metric->getMetricType()
        ]);

        return $metric;
    }

    /**
     * Update supplier rating
     *
     * @param int $supplierId
     * @param float $score
     * @return SupplierRating
     */
    public function updateSupplierRating(int $supplierId, float $score): SupplierRating
    {
        $rating = $this->determineRating($score);

        // Check if rating exists
        $existingRating = $this->getSupplierRating($supplierId);

        $ratingData = [
            'supplier_id' => $supplierId,
            'current_score' => $score,
            'rating' => $rating,
            'rating_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existingRating) {
            $this->dbal->update('supplier_ratings', $ratingData, ['supplier_id' => $supplierId]);
            $ratingId = $existingRating->getId();
        } else {
            $ratingData['created_at'] = date('Y-m-d H:i:s');
            $ratingId = $this->dbal->insert('supplier_ratings', $ratingData);
        }

        $supplierRating = new SupplierRating(array_merge($ratingData, ['id' => $ratingId]));

        $this->logger->info('Supplier rating updated', [
            'supplier_id' => $supplierId,
            'score' => $score,
            'rating' => $rating
        ]);

        $this->eventDispatcher->dispatch(new SupplierRatingUpdatedEvent($supplierRating));

        return $supplierRating;
    }

    /**
     * Get evaluation by ID
     *
     * @param int $evaluationId
     * @return SupplierEvaluation
     */
    public function getEvaluation(int $evaluationId): SupplierEvaluation
    {
        $sql = "SELECT * FROM supplier_evaluations WHERE id = ?";
        $data = $this->dbal->fetchOne($sql, [$evaluationId]);

        if (!$data) {
            throw new SupplierEvaluationNotFoundException($evaluationId);
        }

        return new SupplierEvaluation($data);
    }

    /**
     * Get supplier rating
     *
     * @param int $supplierId
     * @return SupplierRating|null
     */
    public function getSupplierRating(int $supplierId): ?SupplierRating
    {
        $sql = "SELECT * FROM supplier_ratings WHERE supplier_id = ?";
        $data = $this->dbal->fetchOne($sql, [$supplierId]);

        return $data ? new SupplierRating($data) : null;
    }

    /**
     * Get evaluations for supplier
     *
     * @param int $supplierId
     * @param int $limit
     * @return SupplierEvaluation[]
     */
    public function getSupplierEvaluations(int $supplierId, int $limit = 50): array
    {
        $sql = "
            SELECT * FROM supplier_evaluations
            WHERE supplier_id = ?
            ORDER BY evaluation_date DESC
            LIMIT ?
        ";
        $evaluationsData = $this->dbal->fetchAll($sql, [$supplierId, $limit]);

        $evaluations = [];
        foreach ($evaluationsData as $data) {
            $evaluations[] = new SupplierEvaluation($data);
        }

        return $evaluations;
    }

    /**
     * Get metrics for supplier
     *
     * @param int $supplierId
     * @param string|null $metricType
     * @param int $limit
     * @return SupplierMetric[]
     */
    public function getSupplierMetrics(int $supplierId, ?string $metricType = null, int $limit = 100): array
    {
        if ($metricType) {
            $sql = "
                SELECT * FROM supplier_metrics
                WHERE supplier_id = ? AND metric_type = ?
                ORDER BY metric_date DESC
                LIMIT ?
            ";
            $params = [$supplierId, $metricType, $limit];
        } else {
            $sql = "
                SELECT * FROM supplier_metrics
                WHERE supplier_id = ?
                ORDER BY metric_date DESC
                LIMIT ?
            ";
            $params = [$supplierId, $limit];
        }

        $metricsData = $this->dbal->fetchAll($sql, $params);

        $metrics = [];
        foreach ($metricsData as $data) {
            $metrics[] = new SupplierMetric($data);
        }

        return $metrics;
    }

    /**
     * Get top suppliers by rating
     *
     * @param int $limit
     * @return SupplierRating[]
     */
    public function getTopSuppliers(int $limit = 10): array
    {
        $sql = "
            SELECT * FROM supplier_ratings
            ORDER BY current_score DESC
            LIMIT ?
        ";
        $ratingsData = $this->dbal->fetchAll($sql, [$limit]);

        $ratings = [];
        foreach ($ratingsData as $data) {
            $ratings[] = new SupplierRating($data);
        }

        return $ratings;
    }

    /**
     * Get supplier performance summary
     *
     * @param int $supplierId
     * @param string $periodStart
     * @param string $periodEnd
     * @return array
     */
    public function getPerformanceSummary(int $supplierId, string $periodStart, string $periodEnd): array
    {
        // Get average scores for period
        $sql = "
            SELECT
                AVG(overall_score) as avg_overall,
                AVG(quality_score) as avg_quality,
                AVG(delivery_score) as avg_delivery,
                AVG(price_score) as avg_price,
                AVG(service_score) as avg_service,
                AVG(compliance_score) as avg_compliance,
                COUNT(*) as evaluation_count
            FROM supplier_evaluations
            WHERE supplier_id = ?
              AND evaluation_date BETWEEN ? AND ?
              AND status = 'finalized'
        ";
        $scores = $this->dbal->fetchOne($sql, [$supplierId, $periodStart, $periodEnd]);

        // Get key metrics
        $metricSql = "
            SELECT metric_type, AVG(metric_value) as avg_value
            FROM supplier_metrics
            WHERE supplier_id = ?
              AND metric_date BETWEEN ? AND ?
            GROUP BY metric_type
        ";
        $metrics = $this->dbal->fetchAll($metricSql, [$supplierId, $periodStart, $periodEnd]);

        return [
            'supplier_id' => $supplierId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'scores' => $scores,
            'metrics' => $metrics,
            'current_rating' => $this->getSupplierRating($supplierId)
        ];
    }

    // Private helper methods

    private function validateEvaluationData(array $data): void
    {
        $errors = [];

        if (empty($data['supplier_id']) || !is_numeric($data['supplier_id'])) {
            $errors['supplier_id'] = 'Valid supplier ID is required';
        }

        if (empty($data['evaluator_id']) || !is_numeric($data['evaluator_id'])) {
            $errors['evaluator_id'] = 'Valid evaluator ID is required';
        }

        if (empty($data['period_start']) || empty($data['period_end'])) {
            $errors['period'] = 'Evaluation period dates are required';
        }

        if (!empty($errors)) {
            throw new SupplierPerformanceValidationException('Evaluation validation failed', 0, $errors);
        }
    }

    private function validateMetricData(array $data): void
    {
        $errors = [];

        if (empty($data['supplier_id']) || !is_numeric($data['supplier_id'])) {
            $errors['supplier_id'] = 'Valid supplier ID is required';
        }

        if (empty($data['metric_type'])) {
            $errors['metric_type'] = 'Metric type is required';
        }

        if (!isset($data['metric_value']) || !is_numeric($data['metric_value'])) {
            $errors['metric_value'] = 'Valid metric value is required';
        }

        if (!empty($errors)) {
            throw new SupplierPerformanceValidationException('Metric validation failed', 0, $errors);
        }
    }

    private function calculateOverallScore(array $scores): float
    {
        // Weighted average: Quality 30%, Delivery 25%, Price 20%, Service 15%, Compliance 10%
        $weights = [
            'quality' => 0.30,
            'delivery' => 0.25,
            'price' => 0.20,
            'service' => 0.15,
            'compliance' => 0.10
        ];

        $overallScore = 0.00;
        foreach ($scores as $category => $score) {
            $overallScore += ($score * $weights[$category]);
        }

        return round($overallScore, 2);
    }

    private function determineRating(float $score): string
    {
        if ($score >= 90) return 'excellent';
        if ($score >= 80) return 'good';
        if ($score >= 70) return 'satisfactory';
        if ($score >= 60) return 'needs_improvement';
        return 'poor';
    }

    private function generateEvaluationReference(): string
    {
        $sql = "SELECT COUNT(*) as count FROM supplier_evaluations WHERE DATE(created_at) = CURDATE()";
        $result = $this->dbal->fetchOne($sql);
        $sequence = (int)($result['count'] ?? 0) + 1;
        return 'SPE-' . date('Y') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
