<?php
/**
 * FrontAccounting Supplier Performance Module - Exceptions
 *
 * Exception classes for supplier performance operations.
 *
 * @package FA\Modules\SupplierPerformance
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\SupplierPerformance;

/**
 * Base Supplier Performance Exception
 */
class SupplierPerformanceException extends \Exception
{
    protected int $entityId;

    public function __construct(
        string $message = "",
        int $entityId = 0,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->entityId = $entityId;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }
}

/**
 * Supplier Evaluation Not Found Exception
 */
class SupplierEvaluationNotFoundException extends SupplierPerformanceException
{
    public function __construct(int $evaluationId, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Supplier evaluation with ID {$evaluationId} not found", $evaluationId, $code, $previous);
    }
}

/**
 * Supplier Performance Validation Exception
 */
class SupplierPerformanceValidationException extends SupplierPerformanceException
{
    protected array $validationErrors;

    public function __construct(
        string $message = "",
        int $entityId = 0,
        array $validationErrors = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $entityId, $code, $previous);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function hasValidationErrors(): bool
    {
        return !empty($this->validationErrors);
    }

    public function getValidationError(string $field): ?string
    {
        return $this->validationErrors[$field] ?? null;
    }
}

/**
 * Supplier Metric Not Found Exception
 */
class SupplierMetricNotFoundException extends SupplierPerformanceException
{
    public function __construct(int $metricId, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Supplier metric with ID {$metricId} not found", $metricId, $code, $previous);
    }
}

/**
 * Invalid Evaluation Status Exception
 */
class InvalidEvaluationStatusException extends SupplierPerformanceException
{
    private string $currentStatus;
    private string $attemptedAction;

    public function __construct(
        int $evaluationId,
        string $currentStatus,
        string $attemptedAction,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = "Cannot {$attemptedAction} evaluation {$evaluationId} with status '{$currentStatus}'";
        parent::__construct($message, $evaluationId, $code, $previous);
        $this->currentStatus = $currentStatus;
        $this->attemptedAction = $attemptedAction;
    }

    public function getCurrentStatus(): string
    {
        return $this->currentStatus;
    }

    public function getAttemptedAction(): string
    {
        return $this->attemptedAction;
    }
}
