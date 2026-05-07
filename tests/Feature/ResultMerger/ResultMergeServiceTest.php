<?php

namespace Tests\Feature\ResultMerger;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Enums\ResultIssueType;
use App\Models\ExamScore;
use App\Models\ImportBatch;
use App\Models\MergedResult;
use App\Models\ResultIssue;
use App\Models\TestScore;
use App\Services\Results\ResultMergeService;

class ResultMergeServiceTest extends ResultMergerTestCase
{
    public function test_test_and_exam_scores_are_merged_by_student_id(): void
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

        $mergeBatch = ImportBatch::query()->create([
            'name' => 'Merge Batch',
            'type' => ImportBatchType::Merge,
            'status' => ImportBatchStatus::Processing,
        ]);

        TestScore::query()->create([
            'import_batch_id' => $testBatch->id,
            'student_id' => '1001',
            'matric_no' => 'CSC/001',
            'first_name' => 'Ada',
            'last_name' => 'Okon',
            'level' => '100',
            'college' => 'Science',
            'department' => 'Physics',
            'test_score' => 35,
            'is_valid' => true,
        ]);

        ExamScore::query()->create([
            'import_batch_id' => $examBatch->id,
            'student_id' => '1001',
            'matric_no' => 'CSC/001',
            'first_name' => 'Ada',
            'last_name' => 'Okon',
            'level' => '100',
            'college' => 'Science',
            'department' => 'Physics',
            'exam_score' => 55,
            'is_valid' => true,
        ]);

        app(ResultMergeService::class)->merge(
            mergeBatch: $mergeBatch,
            testBatch: $testBatch,
            examBatch: $examBatch,
            matchBy: 'student_id'
        );

        $this->assertEquals(1, MergedResult::query()->count());

        $this->assertDatabaseHas('merged_results', [
            'student_id' => '1001',
            'test_score' => 35,
            'exam_score' => 55,
            'total_score' => 90,
            'grade' => 'A',
            'is_valid' => true,
        ]);
    }

    public function test_missing_exam_record_creates_invalid_merged_result_and_issue(): void
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

        $mergeBatch = ImportBatch::query()->create([
            'name' => 'Merge Batch',
            'type' => ImportBatchType::Merge,
            'status' => ImportBatchStatus::Processing,
        ]);

        TestScore::query()->create([
            'import_batch_id' => $testBatch->id,
            'student_id' => '1001',
            'matric_no' => 'CSC/001',
            'first_name' => 'Ada',
            'last_name' => 'Okon',
            'level' => '100',
            'college' => 'Science',
            'department' => 'Physics',
            'test_score' => 35,
            'is_valid' => true,
        ]);

        app(ResultMergeService::class)->merge(
            mergeBatch: $mergeBatch,
            testBatch: $testBatch,
            examBatch: $examBatch,
            matchBy: 'student_id'
        );

        $this->assertDatabaseHas('merged_results', [
            'student_id' => '1001',
            'exam_score' => null,
            'is_valid' => false,
            'validation_message' => 'No matching exam record found.',
        ]);

        $this->assertDatabaseHas('result_issues', [
            'import_batch_id' => $mergeBatch->id,
            'type' => ResultIssueType::MissingExamRecord->value,
            'student_id' => '1001',
        ]);
    }

    public function test_exam_record_without_test_creates_issue(): void
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

        $mergeBatch = ImportBatch::query()->create([
            'name' => 'Merge Batch',
            'type' => ImportBatchType::Merge,
            'status' => ImportBatchStatus::Processing,
        ]);

        TestScore::query()->create([
            'import_batch_id' => $testBatch->id,
            'student_id' => '1001',
            'matric_no' => 'CSC/001',
            'test_score' => 30,
            'is_valid' => true,
        ]);

        ExamScore::query()->create([
            'import_batch_id' => $examBatch->id,
            'student_id' => '1002',
            'matric_no' => 'CSC/002',
            'exam_score' => 50,
            'is_valid' => true,
        ]);

        app(ResultMergeService::class)->merge(
            mergeBatch: $mergeBatch,
            testBatch: $testBatch,
            examBatch: $examBatch,
            matchBy: 'student_id'
        );

        $this->assertDatabaseHas('result_issues', [
            'import_batch_id' => $mergeBatch->id,
            'type' => ResultIssueType::MissingTestRecord->value,
            'student_id' => '1002',
        ]);
    }
}