<?php

namespace App\Services\Exports;

use App\Exports\MergedResultsExport;
use App\Models\MergedResult;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class MergedResultExcelExportService
{
    public function export(?int $mergeBatchId = null, bool $validOnly = false): string
    {
        $query = MergedResult::query()
            ->when($mergeBatchId, fn($query) => $query->where('merge_batch_id', $mergeBatchId))
            ->when($validOnly, fn($query) => $query->where('is_valid', true));

        if (!$query->exists()) {
            throw new RuntimeException('No merged results found for the selected export options.');
        }

        $filePath = 'exports/merged-results-' . now()->format('Y-m-d-His') . '.xlsx';

        Excel::store(
            new MergedResultsExport(
                mergeBatchId: $mergeBatchId,
                validOnly: $validOnly,
            ),
            $filePath,
            'local'
        );

        return $filePath;
    }
}