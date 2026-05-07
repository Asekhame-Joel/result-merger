<?php

namespace App\Jobs;

use App\Enums\ImportBatchStatus;
use App\Models\ImportBatch;
use App\Services\Results\ResultMergeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class MergeResultBatchJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(
        public int $mergeBatchId,
        public int $testBatchId,
        public int $examBatchId,
        public string $matchBy = 'student_id',
    ) {
    }

    public function handle(): void
    {
        $mergeBatch = ImportBatch::findOrFail($this->mergeBatchId);
        $testBatch = ImportBatch::findOrFail($this->testBatchId);
        $examBatch = ImportBatch::findOrFail($this->examBatchId);

        $mergeBatch->markProcessing();

        try {
            app(ResultMergeService::class)->merge(
                mergeBatch: $mergeBatch,
                testBatch: $testBatch,
                examBatch: $examBatch,
                matchBy: $this->matchBy,
            );

            $mergeBatch->markCompleted();
        } catch (Throwable $exception) {
            $mergeBatch->markFailed($exception->getMessage());

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        ImportBatch::query()
            ->whereKey($this->mergeBatchId)
            ->update([
                'status' => ImportBatchStatus::Failed,
                'failed_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);
    }
}