<?php

namespace App\Jobs;

use App\Enums\ImportBatchStatus;
use App\Imports\TestScoresImport;
use App\Models\ImportBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessTestScoreImportJob implements ShouldQueue
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

            app(\App\Services\Imports\TestScoreCsvImportService::class)->import($batch);

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