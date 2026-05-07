<?php

namespace Tests\Feature\ResultMerger;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Models\ExamScore;
use App\Models\ImportBatch;
use App\Models\MergedResult;
use App\Models\ResultIssue;
use App\Models\TestScore;
use App\Services\Cleanup\ResultCleanupService;
use RuntimeException;

class ResultCleanupServiceTest extends ResultMergerTestCase
{
    public function test_processing_batch_cannot_be_deleted(): void
    {
        $batch = ImportBatch::query()->create([
            'name' => 'Processing Batch',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Processing,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You cannot delete a pending or processing batch.');

        app(ResultCleanupService::class)->deleteBatch($batch);
    }

    public function test_all_processing_data_can_be_reset(): void
    {
        $testBatch = ImportBatch::query()->create([
            'name' => 'Test Batch',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Completed,
        ]);

        $examBatch = ImportBatch::query()->create([
            'name' => 'Exam Batch',
            'type' => ImportBatchType::Exam,
            'status' => ImportBatchStatus::Completed,
        ]);

        TestScore::query()->create([
            'import_batch_id' => $testBatch->id,
            'student_id' => '1001',
            'test_score' => 35,
        ]);

        ExamScore::query()->create([
            'import_batch_id' => $examBatch->id,
            'student_id' => '1001',
            'exam_score' => 55,
        ]);

        MergedResult::query()->create([
            'student_id' => '1001',
            'total_score' => 90,
        ]);

        ResultIssue::query()->create([
            'type' => 'invalid_test_score',
            'severity' => 'error',
            'status' => 'open',
            'message' => 'Invalid score.',
        ]);

        app(ResultCleanupService::class)->resetProcessingData();

        $this->assertEquals(0, ImportBatch::query()->count());
        $this->assertEquals(0, TestScore::query()->count());
        $this->assertEquals(0, ExamScore::query()->count());
        $this->assertEquals(0, MergedResult::query()->count());
        $this->assertEquals(0, ResultIssue::query()->count());
    }

    public function test_all_issues_can_be_deleted_without_deleting_scores(): void
    {
        $batch = ImportBatch::query()->create([
            'name' => 'Test Batch',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Completed,
        ]);

        TestScore::query()->create([
            'import_batch_id' => $batch->id,
            'student_id' => '1001',
            'test_score' => 35,
        ]);

        ResultIssue::query()->create([
            'type' => 'invalid_test_score',
            'severity' => 'error',
            'status' => 'open',
            'message' => 'Invalid score.',
        ]);

        app(ResultCleanupService::class)->deleteAllIssues();

        $this->assertEquals(1, TestScore::query()->count());
        $this->assertEquals(0, ResultIssue::query()->count());
    }
}