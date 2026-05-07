<?php

namespace App\Imports;

use App\Models\ImportBatch;
use App\Services\Imports\TestScoreImportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TestScoresImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function __construct(
        protected ImportBatch $batch
    ) {
    }

    public function collection(Collection $rows): void
    {
        app(TestScoreImportService::class)->importRows($this->batch, $rows);
    }
}