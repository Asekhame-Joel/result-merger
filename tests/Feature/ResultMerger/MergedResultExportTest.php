<?php

namespace Tests\Feature\ResultMerger;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Models\ImportBatch;
use App\Models\MergedResult;
use App\Services\Exports\MergedResultCsvExportService;
use App\Services\Exports\MergedResultExcelExportService;
use Illuminate\Support\Facades\Storage;

class MergedResultExportTest extends ResultMergerTestCase
{
    public function test_merged_results_can_be_exported_as_csv(): void
    {
        Storage::fake('local');

        $mergeBatch = ImportBatch::query()->create([
            'name' => 'Merge Batch',
            'type' => ImportBatchType::Merge,
            'status' => ImportBatchStatus::Completed,
        ]);

        MergedResult::query()->create([
            'merge_batch_id' => $mergeBatch->id,
            'student_id' => '1001',
            'matric_no' => 'CSC/001',
            'first_name' => 'Ada',
            'last_name' => 'Okon',
            'level' => '100',
            'college' => 'Science',
            'department' => 'Physics',
            'test_score' => 35,
            'exam_score' => 55,
            'total_score' => 90,
            'is_valid' => true,
        ]);

        $path = app(MergedResultCsvExportService::class)->export($mergeBatch->id);

        Storage::disk('local')->assertExists($path);

        $content = Storage::disk('local')->get($path);

        $this->assertStringContainsString('student_id,matric_no,first_name,last_name,Level,college,department,test_score,exam_score,total_score', $content);
        $this->assertStringContainsString('1001,CSC/001,Ada,Okon,100,Science,Physics,35.00,55.00,90.00', $content);
    }

    public function test_merged_results_can_be_exported_as_excel(): void
    {
        Storage::fake('local');

        $mergeBatch = ImportBatch::query()->create([
            'name' => 'Merge Batch',
            'type' => ImportBatchType::Merge,
            'status' => ImportBatchStatus::Completed,
        ]);

        MergedResult::query()->create([
            'merge_batch_id' => $mergeBatch->id,
            'student_id' => '1001',
            'matric_no' => 'CSC/001',
            'first_name' => 'Ada',
            'last_name' => 'Okon',
            'level' => '100',
            'college' => 'Science',
            'department' => 'Physics',
            'test_score' => 35,
            'exam_score' => 55,
            'total_score' => 90,
            'is_valid' => true,
        ]);

        $path = app(MergedResultExcelExportService::class)->export($mergeBatch->id);

        Storage::disk('local')->assertExists($path);
        $this->assertStringEndsWith('.xlsx', $path);
    }
}