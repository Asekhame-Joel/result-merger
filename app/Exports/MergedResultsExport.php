<?php

namespace App\Exports;

use App\Models\MergedResult;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class MergedResultsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected ?int $mergeBatchId = null,
        protected bool $validOnly = false,
    ) {
    }

    public function query(): Builder
    {
        return MergedResult::query()
            ->when($this->mergeBatchId, fn(Builder $query) => $query->where('merge_batch_id', $this->mergeBatchId))
            ->when($this->validOnly, fn(Builder $query) => $query->where('is_valid', true))
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
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
        ];
    }

    public function map($result): array
    {
        return [
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
        ];
    }

    public function title(): string
    {
        return 'Merged Results';
    }
}