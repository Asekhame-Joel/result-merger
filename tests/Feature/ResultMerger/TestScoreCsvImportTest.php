<?php

namespace Tests\Feature\ResultMerger;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Enums\ResultIssueType;
use App\Models\ImportBatch;
use App\Models\ResultIssue;
use App\Models\TestScore;
use App\Services\Imports\TestScoreCsvImportService;
use Illuminate\Support\Facades\Storage;

class TestScoreCsvImportTest extends ResultMergerTestCase
{
    public function test_test_scores_are_imported_from_csv(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('imports/test-scores/test.csv', implode("\n", [
            'student_id,matric_no,first_name,last_name,Level,college,department,test_score',
            '1001,CSC/001,Ada,Okon,100,Science,Physics,35',
            '1002,CSC/002,John,Ali,100,Science,Physics,28',
        ]));

        $batch = ImportBatch::query()->create([
            'name' => 'Test Upload',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Processing,
            'file_path' => 'imports/test-scores/test.csv',
            'disk' => 'local',
        ]);

        app(TestScoreCsvImportService::class)->import($batch);

        $this->assertEquals(2, TestScore::query()->count());

        $this->assertDatabaseHas('test_scores', [
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

        $this->assertEquals(2, $batch->fresh()->successful_rows);
    }

    public function test_invalid_test_score_creates_issue(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('imports/test-scores/invalid.csv', implode("\n", [
            'student_id,matric_no,first_name,last_name,Level,college,department,test_score',
            '1001,CSC/001,Ada,Okon,100,Science,Physics,45',
        ]));

        $batch = ImportBatch::query()->create([
            'name' => 'Invalid Test Upload',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Processing,
            'file_path' => 'imports/test-scores/invalid.csv',
            'disk' => 'local',
        ]);

        app(TestScoreCsvImportService::class)->import($batch);

        $this->assertDatabaseHas('test_scores', [
            'student_id' => '1001',
            'is_valid' => false,
            'validation_message' => 'Test score cannot exceed 40.00.',
        ]);

        $this->assertDatabaseHas('result_issues', [
            'import_batch_id' => $batch->id,
            'type' => ResultIssueType::InvalidTestScore->value,
            'student_id' => '1001',
        ]);
    }

    public function test_duplicate_student_ids_are_detected(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('imports/test-scores/duplicates.csv', implode("\n", [
            'student_id,matric_no,first_name,last_name,Level,college,department,test_score',
            '1001,CSC/001,Ada,Okon,100,Science,Physics,35',
            '1001,CSC/002,John,Ali,100,Science,Physics,28',
        ]));

        $batch = ImportBatch::query()->create([
            'name' => 'Duplicate Test Upload',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Processing,
            'file_path' => 'imports/test-scores/duplicates.csv',
            'disk' => 'local',
        ]);

        app(TestScoreCsvImportService::class)->import($batch);

        $this->assertEquals(2, TestScore::query()->where('is_valid', false)->count());

        $this->assertEquals(
            2,
            ResultIssue::query()
                ->where('type', ResultIssueType::DuplicateStudentId)
                ->count()
        );
    }
}