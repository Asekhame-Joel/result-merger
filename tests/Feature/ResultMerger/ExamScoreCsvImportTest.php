<?php

namespace Tests\Feature\ResultMerger;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Enums\ResultIssueType;
use App\Models\ExamScore;
use App\Models\ImportBatch;
use App\Services\Imports\ExamScoreCsvImportService;
use Illuminate\Support\Facades\Storage;

class ExamScoreCsvImportTest extends ResultMergerTestCase
{
    public function test_exam_scores_are_imported_from_csv_with_exam_score_header(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('imports/exam-scores/exam.csv', implode("\n", [
            'student_id,matric_no,first_name,last_name,Level,college,department,exam score',
            '1001,CSC/001,Ada,Okon,100,Science,Physics,55',
            '1002,CSC/002,John,Ali,100,Science,Physics,48',
        ]));

        $batch = ImportBatch::query()->create([
            'name' => 'Exam Upload',
            'type' => ImportBatchType::Exam,
            'status' => ImportBatchStatus::Processing,
            'file_path' => 'imports/exam-scores/exam.csv',
            'disk' => 'local',
        ]);

        app(ExamScoreCsvImportService::class)->import($batch);

        $this->assertEquals(2, ExamScore::query()->count());

        $this->assertDatabaseHas('exam_scores', [
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
    }

    public function test_invalid_exam_score_creates_issue(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('imports/exam-scores/invalid.csv', implode("\n", [
            'student_id,matric_no,first_name,last_name,Level,college,department,exam score',
            '1001,CSC/001,Ada,Okon,100,Science,Physics,70',
        ]));

        $batch = ImportBatch::query()->create([
            'name' => 'Invalid Exam Upload',
            'type' => ImportBatchType::Exam,
            'status' => ImportBatchStatus::Processing,
            'file_path' => 'imports/exam-scores/invalid.csv',
            'disk' => 'local',
        ]);

        app(ExamScoreCsvImportService::class)->import($batch);

        $this->assertDatabaseHas('exam_scores', [
            'student_id' => '1001',
            'is_valid' => false,
            'validation_message' => 'Exam score cannot exceed 60.00.',
        ]);

        $this->assertDatabaseHas('result_issues', [
            'import_batch_id' => $batch->id,
            'type' => ResultIssueType::InvalidExamScore->value,
            'student_id' => '1001',
        ]);
    }
}