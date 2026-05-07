<?php

namespace App\Jobs;

use App\Enums\ImportBatchStatus;
use App\Models\ImportBatch;
use App\Services\Imports\ExamScoreCsvImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessExamScoreImportJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(
        public int $batchId
    ) {
    }

    public function handle(): void
    {
        $batch = ImportBatch::findOrFail($this->batchId);

        $batch->markProcessing();

        try {
            $extension = strtolower(pathinfo($batch->file_path, PATHINFO_EXTENSION));

            if (!in_array($extension, ['csv', 'txt'], true)) {
                throw new \RuntimeException('Please upload CSV only. Save your Excel file as CSV UTF-8 before uploading.');
            }

            app(ExamScoreCsvImportService::class)->import($batch);

            $batch->markCompleted();
        } catch (Throwable $exception) {
            $batch->markFailed($exception->getMessage());

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        ImportBatch::query()
            ->whereKey($this->batchId)
            ->update([
                'status' => ImportBatchStatus::Failed,
                'failed_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);
    }
}