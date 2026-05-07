<?php

namespace Tests\Feature\ResultMerger;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Models\ImportBatch;
use App\Models\ResultIssue;

class ImportBatchTest extends ResultMergerTestCase
{
    public function test_import_batch_progress_percentage_is_calculated(): void
    {
        $batch = ImportBatch::query()->create([
            'name' => 'Test Batch',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Processing,
            'total_rows' => 100,
            'processed_rows' => 25,
        ]);

        $this->assertEquals(25, $batch->progressPercentage());
    }

    public function test_import_batch_can_be_marked_completed_with_issues(): void
    {
        $batch = ImportBatch::query()->create([
            'name' => 'Test Batch',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Processing,
            'issue_count' => 1,
        ]);

        $batch->markCompleted();

        $this->assertEquals(
            ImportBatchStatus::CompletedWithIssues,
            $batch->fresh()->status
        );
    }

    public function test_import_batch_can_be_marked_failed(): void
    {
        $batch = ImportBatch::query()->create([
            'name' => 'Test Batch',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Processing,
        ]);

        $batch->markFailed('Something went wrong.');

        $batch = $batch->fresh();

        $this->assertEquals(ImportBatchStatus::Failed, $batch->status);
        $this->assertEquals('Something went wrong.', $batch->error_message);
        $this->assertNotNull($batch->failed_at);
    }
}