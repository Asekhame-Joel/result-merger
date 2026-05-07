<?php

namespace App\Imports;

use App\Models\ImportBatch;
use App\Services\Imports\TestScoreImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;

class TestScoresImport implements ToCollection, SkipsEmptyRows
{
    public function __construct(
        protected ImportBatch $batch
    ) {
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            app(TestScoreImportService::class)->importRows($this->batch, collect());

            return;
        }

        $headingRow = $rows->first();

        $headings = collect($headingRow)
            ->map(fn($heading): string => $this->normalizeHeading($heading))
            ->values();

        $dataRows = $rows
            ->skip(1)
            ->filter(fn($row): bool => collect($row)->filter(fn($value) => filled($value))->isNotEmpty())
            ->map(function ($row) use ($headings): array {
                $values = collect($row)->values();

                return $headings
                    ->mapWithKeys(fn(string $heading, int $index): array => [
                        $heading => $values->get($index),
                    ])
                    ->all();
            })
            ->values();

        app(TestScoreImportService::class)->importRows($this->batch, $dataRows);
    }

    protected function normalizeHeading(mixed $heading): string
    {
        return Str::of((string) $heading)
            ->trim()
            ->lower()
            ->replace([' ', '-', '.'], '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->toString();
    }
}