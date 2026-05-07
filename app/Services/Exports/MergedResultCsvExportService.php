<?php

namespace App\Services\Exports;

use App\Models\ImportBatch;
use App\Models\MergedResult;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SplFileObject;

class MergedResultCsvExportService
{
    public function export(?int $mergeBatchId = null, bool $validOnly = false): string
    {
        $fileName = 'exports/merged-results-' . now()->format('Y-m-d-His') . '.csv';

        Storage::disk('local')->makeDirectory('exports');

        $path = Storage::disk('local')->path($fileName);

        $file = new SplFileObject($path, 'w');

        $file->fputcsv([
            'student_id',
            'matric_no',
            'first_name',
            'last_name',
            'Level',
            'college',
            'department',
            'test_score',
            'exam_score',
            'total_score',
        ]);

        $query = MergedResult::query()
            ->when($mergeBatchId, fn($query) => $query->where('merge_batch_id', $mergeBatchId))
            ->when($validOnly, fn($query) => $query->where('is_valid', true))
            ->orderBy('id');

        if (!$query->exists()) {
            throw new RuntimeException('No merged results found for the selected export options.');
        }

        $query->chunkById(1000, function ($results) use ($file): void {
            foreach ($results as $result) {
                $file->fputcsv([
                    $result->student_id,
                    $result->matric_no,
                    $result->first_name,
                    $result->last_name,
                    $result->level,
                    $result->college,
                    $result->department,
                    $result->test_score,
                    $result->exam_score,
                    $result->total_score,
                ]);
            }
        });

        return $fileName;
    }

    public function exportForBatch(ImportBatch $batch, bool $validOnly = false): string
    {
        return $this->export($batch->id, $validOnly);
    }
}