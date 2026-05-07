<?php

namespace Tests\Feature\ResultMerger;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Enums\ResultIssueSeverity;
use App\Enums\ResultIssueStatus;
use App\Enums\ResultIssueType;
use App\Filament\Pages\DataCleanup;
use App\Filament\Pages\ExportResults;
use App\Filament\Pages\MergeResults;
use App\Filament\Pages\UploadExamScores;
use App\Filament\Pages\UploadTestScores;
use App\Filament\Resources\ExamScores\ExamScoreResource;
use App\Filament\Resources\ImportBatches\ImportBatchResource;
use App\Filament\Resources\MergedResults\MergedResultResource;
use App\Filament\Resources\ResultIssues\ResultIssueResource;
use App\Filament\Resources\TestScores\TestScoreResource;
use App\Models\ExamScore;
use App\Models\ImportBatch;
use App\Models\MergedResult;
use App\Models\ResultIssue;
use App\Models\TestScore;

class FilamentViewPagesTest extends ResultMergerTestCase
{
    public function test_guest_can_view_admin_login_page(): void
    {
        $this->get('/admin/login')
            ->assertSuccessful()
            ->assertSee('Result Merger')
            ->assertSee('Welcome Back');
    }

    public function test_admin_can_view_dashboard(): void
    {
        $this->actingAsPanelUser();

        $this->get('/admin')
            ->assertSuccessful();
    }

    public function test_admin_can_view_upload_test_scores_page(): void
    {
        $this->actingAsPanelUser();

        $this->get(UploadTestScores::getUrl())
            ->assertSuccessful()
            ->assertSee('Upload Test Scores');
    }

    public function test_admin_can_view_upload_exam_scores_page(): void
    {
        $this->actingAsPanelUser();

        $this->get(UploadExamScores::getUrl())
            ->assertSuccessful()
            ->assertSee('Upload Exam Scores');
    }

    public function test_admin_can_view_merge_results_page(): void
    {
        $this->actingAsPanelUser();

        $this->get(MergeResults::getUrl())
            ->assertSuccessful()
            ->assertSee('Merge Results');
    }

    public function test_admin_can_view_export_results_page(): void
    {
        $this->actingAsPanelUser();

        $this->get(ExportResults::getUrl())
            ->assertSuccessful()
            ->assertSee('Export Results');
    }

    public function test_admin_can_view_data_cleanup_page(): void
    {
        $this->actingAsPanelUser();

        $this->get(DataCleanup::getUrl())
            ->assertSuccessful()
            ->assertSee('Data Cleanup');
    }

    public function test_admin_can_view_import_batch_detail_page(): void
    {
        $this->actingAsPanelUser();

        $batch = ImportBatch::query()->create([
            'name' => 'PHY104 Test Scores Upload',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Completed,
            'total_rows' => 10,
            'processed_rows' => 10,
            'successful_rows' => 10,
            'failed_rows' => 0,
            'issue_count' => 0,
        ]);

        $this->get(ImportBatchResource::getUrl('view', [
            'record' => $batch,
        ]))
            ->assertSuccessful()
            ->assertSee('PHY104 Test Scores Upload')
            ->assertSee('Completed');
    }

    public function test_admin_can_view_test_score_detail_page(): void
    {
        $this->actingAsPanelUser();

        $batch = ImportBatch::query()->create([
            'name' => 'PHY104 Test Scores Upload',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Completed,
        ]);

        $testScore = TestScore::query()->create([
            'import_batch_id' => $batch->id,
            'student_id' => '1028113',
            'matric_no' => '23/026480/HSC',
            'first_name' => 'John',
            'last_name' => 'Okafor',
            'level' => '100',
            'college' => 'Science',
            'department' => 'Physics',
            'test_score' => 35,
            'row_number' => 2,
            'is_valid' => true,
        ]);

        $this->get(TestScoreResource::getUrl('view', [
            'record' => $testScore,
        ]))
            ->assertSuccessful()
            ->assertSee('1028113')
            ->assertSee('23/026480/HSC')
            ->assertSee('John')
            ->assertSee('Physics');
    }

    public function test_admin_can_view_exam_score_detail_page(): void
    {
        $this->actingAsPanelUser();

        $batch = ImportBatch::query()->create([
            'name' => 'PHY104 Exam Scores Upload',
            'type' => ImportBatchType::Exam,
            'status' => ImportBatchStatus::Completed,
        ]);

        $examScore = ExamScore::query()->create([
            'import_batch_id' => $batch->id,
            'student_id' => '1028113',
            'matric_no' => '23/026480/HSC',
            'first_name' => 'John',
            'last_name' => 'Okafor',
            'level' => '100',
            'college' => 'Science',
            'department' => 'Physics',
            'exam_score' => 55,
            'row_number' => 2,
            'is_valid' => true,
        ]);

        $this->get(ExamScoreResource::getUrl('view', [
            'record' => $examScore,
        ]))
            ->assertSuccessful()
            ->assertSee('1028113')
            ->assertSee('23/026480/HSC')
            ->assertSee('John')
            ->assertSee('Physics');
    }

    public function test_admin_can_view_merged_result_detail_page(): void
    {
        $this->actingAsPanelUser();

        $mergeBatch = ImportBatch::query()->create([
            'name' => 'PHY104 Merge Batch',
            'type' => ImportBatchType::Merge,
            'status' => ImportBatchStatus::Completed,
        ]);

        $mergedResult = MergedResult::query()->create([
            'merge_batch_id' => $mergeBatch->id,
            'student_id' => '1028113',
            'matric_no' => '23/026480/HSC',
            'first_name' => 'John',
            'last_name' => 'Okafor',
            'level' => '100',
            'college' => 'Science',
            'department' => 'Physics',
            'test_score' => 35,
            'exam_score' => 55,
            'total_score' => 90,
            'grade' => 'A',
            'remark' => 'Excellent',
            'grade_point' => 5.00,
            'is_valid' => true,
        ]);

        $this->get(MergedResultResource::getUrl('view', [
            'record' => $mergedResult,
        ]))
            ->assertSuccessful()
            ->assertSee('1028113')
            ->assertSee('John')
            ->assertSee('Okafor')
            ->assertSee('Physics')
            ->assertSee('Excellent');

        $this->assertDatabaseHas('merged_results', [
            'id' => $mergedResult->id,
            'student_id' => '1028113',
            'matric_no' => '23/026480/HSC',
            'first_name' => 'John',
            'last_name' => 'Okafor',
            'department' => 'Physics',
            'grade' => 'A',
            'remark' => 'Excellent',
        ]);
    }

    public function test_admin_can_view_result_issue_detail_page(): void
    {
        $this->actingAsPanelUser();

        $batch = ImportBatch::query()->create([
            'name' => 'PHY104 Test Scores Upload',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::CompletedWithIssues,
        ]);

        $issue = ResultIssue::query()->create([
            'import_batch_id' => $batch->id,
            'type' => ResultIssueType::InvalidTestScore,
            'severity' => ResultIssueSeverity::Error,
            'status' => ResultIssueStatus::Open,
            'message' => 'Test score cannot exceed 40.00.',
            'student_id' => '1028113',
            'matric_no' => '23/026480/HSC',
            'level' => '100',
            'department' => 'Physics',
            'row_number' => 6,
        ]);

        $this->get(ResultIssueResource::getUrl('view', [
            'record' => $issue,
        ]))
            ->assertSuccessful()
            ->assertSee('Invalid Test Score')
            ->assertSee('Test score cannot exceed 40.00.')
            ->assertSee('1028113')
            ->assertSee('Physics');
    }
}
