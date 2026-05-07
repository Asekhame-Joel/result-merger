<?php

namespace Tests\Feature\ResultMerger;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Models\ImportBatch;
use App\Services\Imports\ImportUploadGuard;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImportUploadGuardTest extends ResultMergerTestCase
{
    public function test_csv_upload_hash_is_generated(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('imports/test.csv', 'student_id,test_score' . PHP_EOL . '1001,35');

        $hash = app(ImportUploadGuard::class)->validateCsvUpload('local', 'imports/test.csv');

        $this->assertNotEmpty($hash);
        $this->assertEquals(64, strlen($hash));
    }

    public function test_non_csv_file_is_rejected(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('imports/test.xlsx', 'fake content');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only CSV files are allowed');

        app(ImportUploadGuard::class)->validateCsvUpload('local', 'imports/test.xlsx');
    }

    public function test_completed_duplicate_file_is_rejected(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('imports/test.csv', 'student_id,test_score' . PHP_EOL . '1001,35');

        $hash = app(ImportUploadGuard::class)->validateCsvUpload('local', 'imports/test.csv');

        ImportBatch::query()->create([
            'name' => 'Old Import',
            'type' => ImportBatchType::Test,
            'status' => ImportBatchStatus::Completed,
            'file_hash' => $hash,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already been imported');

        app(ImportUploadGuard::class)->preventAnyDuplicate(ImportBatchType::Test, $hash);
    }
}