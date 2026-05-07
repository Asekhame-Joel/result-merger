<?php

namespace App\Services\Cleanup;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Models\ExamScore;
use App\Models\ImportBatch;
use App\Models\MergedResult;
use App\Models\ResultIssue;
use App\Models\TestScore;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ResultCleanupService
{
    public function deleteBatch(ImportBatch $batch): void
    {
        if (
            in_array($batch->status, [
                ImportBatchStatus::Pending,
                ImportBatchStatus::Processing,
            ], true)
        ) {
            throw new RuntimeException('You cannot delete a pending or processing batch.');
        }

        DB::transaction(function () use ($batch): void {
            match ($batch->type) {
                ImportBatchType::Test => $this->deleteTestBatch($batch),
                ImportBatchType::Exam => $this->deleteExamBatch($batch),
                ImportBatchType::Merge => $this->deleteMergeBatch($batch),
                ImportBatchType::Export => $batch->delete(),
            };
        });
    }

    public function deleteAllTestUploads(): void
    {
        DB::transaction(function (): void {
            $testBatchIds = ImportBatch::query()
                ->where('type', ImportBatchType::Test)
                ->pluck('id');

            ResultIssue::query()
                ->whereIn('import_batch_id', $testBatchIds)
                ->delete();

            MergedResult::query()
                ->whereIn('test_import_batch_id', $testBatchIds)
                ->delete();

            TestScore::query()
                ->whereIn('import_batch_id', $testBatchIds)
                ->delete();

            ImportBatch::query()
                ->whereIn('id', $testBatchIds)
                ->delete();
        });
    }

    public function deleteAllExamUploads(): void
    {
        DB::transaction(function (): void {
            $examBatchIds = ImportBatch::query()
                ->where('type', ImportBatchType::Exam)
                ->pluck('id');

            ResultIssue::query()
                ->whereIn('import_batch_id', $examBatchIds)
                ->delete();

            MergedResult::query()
                ->whereIn('exam_import_batch_id', $examBatchIds)
                ->delete();

            ExamScore::query()
                ->whereIn('import_batch_id', $examBatchIds)
                ->delete();

            ImportBatch::query()
                ->whereIn('id', $examBatchIds)
                ->delete();
        });
    }

    public function deleteAllMergedResults(): void
    {
        DB::transaction(function (): void {
            $mergeBatchIds = ImportBatch::query()
                ->where('type', ImportBatchType::Merge)
                ->pluck('id');

            ResultIssue::query()
                ->whereIn('import_batch_id', $mergeBatchIds)
                ->delete();

            MergedResult::query()
                ->whereIn('merge_batch_id', $mergeBatchIds)
                ->delete();

            ImportBatch::query()
                ->whereIn('id', $mergeBatchIds)
                ->delete();
        });
    }

    public function deleteAllIssues(): void
    {
        ResultIssue::query()->delete();
    }

    public function resetProcessingData(): void
    {
        DB::transaction(function (): void {
            ResultIssue::query()->delete();
            MergedResult::query()->delete();
            TestScore::query()->delete();
            ExamScore::query()->delete();
            ImportBatch::query()->delete();
        });
    }

    protected function deleteTestBatch(ImportBatch $batch): void
    {
        ResultIssue::query()
            ->where('import_batch_id', $batch->id)
            ->orWhereIn('test_score_id', TestScore::query()
                ->where('import_batch_id', $batch->id)
                ->select('id'))
            ->delete();

        MergedResult::query()
            ->where('test_import_batch_id', $batch->id)
            ->delete();

        TestScore::query()
            ->where('import_batch_id', $batch->id)
            ->delete();

        $batch->delete();
    }

    protected function deleteExamBatch(ImportBatch $batch): void
    {
        ResultIssue::query()
            ->where('import_batch_id', $batch->id)
            ->orWhereIn('exam_score_id', ExamScore::query()
                ->where('import_batch_id', $batch->id)
                ->select('id'))
            ->delete();

        MergedResult::query()
            ->where('exam_import_batch_id', $batch->id)
            ->delete();

        ExamScore::query()
            ->where('import_batch_id', $batch->id)
            ->delete();

        $batch->delete();
    }

    protected function deleteMergeBatch(ImportBatch $batch): void
    {
        ResultIssue::query()
            ->where('import_batch_id', $batch->id)
            ->delete();

        MergedResult::query()
            ->where('merge_batch_id', $batch->id)
            ->delete();

        $batch->delete();
    }
}