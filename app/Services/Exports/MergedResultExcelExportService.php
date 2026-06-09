<?php

namespace App\Services\Exports;

use App\Models\ImportBatch;
use App\Models\MergedResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MergedResultExcelExportService
{
    public function download(?int $mergeBatchId = null): BinaryFileResponse
    {
        $exportTitle = $this->resolveExportTitle($mergeBatchId);
        $fileName = $this->makeExportFileName($exportTitle);

        return Excel::download(
            new class ($mergeBatchId, $exportTitle) implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell {
            protected int $serialNumber = 0;

            public function __construct(
            protected ?int $mergeBatchId,
            protected string $exportTitle,
            ) {}

            public function collection(): Collection
            {
                return MergedResult::query()
                    ->when($this->mergeBatchId, fn(Builder $query): Builder => $query
                        ->where('merge_batch_id', $this->mergeBatchId))
                    ->orderBy('department')
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->orderBy('student_id')
                    ->get();
            }

            public function headings(): array
            {
                return [
                'Sn',
                'Student ID',
                'First name',
                'Surname',
                'Mat Number',
                'Department',
                'Test Score/30%',
                'Exam Score/70%',
                'Total/100%',
                ];
            }

            public function map($result): array
            {
                $this->serialNumber++;

                return [
                    $this->serialNumber,
                    $result->student_id,
                    $result->first_name,
                    $result->last_name,
                    $result->matric_no,
                    $result->department,
                    $result->test_score,
                    $result->exam_score,
                    $result->total_score,
                ];
            }

            public function startCell(): string
            {
                return 'A3';
            }

            public function styles(Worksheet $sheet): array
            {
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', strtoupper($this->exportTitle . ' MERGE RESULT'));

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->getStyle('A3:I3')->getFont()->setBold(true);
                $sheet->getStyle('A3:I3')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A3:I3')->getAlignment()->setWrapText(true);

                $sheet->getStyle('A:I')->getAlignment()->setVertical('center');

                /*
                 * Fixed professional column widths.
                 * This prevents Department or names from becoming too wide.
                 */
                $sheet->getColumnDimension('A')->setWidth(6);   // Sn
                $sheet->getColumnDimension('B')->setWidth(14);  // Student ID
                $sheet->getColumnDimension('C')->setWidth(18);  // First name
                $sheet->getColumnDimension('D')->setWidth(18);  // Surname
                $sheet->getColumnDimension('E')->setWidth(18);  // Mat Number
                $sheet->getColumnDimension('F')->setWidth(18);  // Department
                $sheet->getColumnDimension('G')->setWidth(14);  // Test Score/30%
                $sheet->getColumnDimension('H')->setWidth(14);  // Exam Score/70%
                $sheet->getColumnDimension('I')->setWidth(14);  // Total/100%

                /*
                 * Wrap long text instead of expanding columns.
                 */
                $sheet->getStyle('C:F')->getAlignment()->setWrapText(true);

                /*
                 * Center short numeric/code columns.
                 */
                $sheet->getStyle('A:B')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('E:E')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('G:I')->getAlignment()->setHorizontal('center');

                return [];
            }
            },
            $fileName
        );
    }

    protected function resolveExportTitle(?int $mergeBatchId = null): string
    {
        if (!$mergeBatchId) {
            return 'Merged Results';
        }

        $mergedResult = MergedResult::query()
            ->where('merge_batch_id', $mergeBatchId)
            ->whereNotNull('test_import_batch_id')
            ->first();

        if (!$mergedResult) {
            $mergedResult = MergedResult::query()
                ->where('merge_batch_id', $mergeBatchId)
                ->whereNotNull('exam_import_batch_id')
                ->first();
        }

        $sourceBatchId = $mergedResult?->test_import_batch_id
            ?: $mergedResult?->exam_import_batch_id;

        $sourceBatch = $sourceBatchId
            ? ImportBatch::query()->find($sourceBatchId)
            : null;

        $uploadedFileName = $sourceBatch?->original_file_name
            ?: $sourceBatch?->file_name
            ?: $sourceBatch?->name
            ?: 'Merged Results';

        return $this->cleanUploadedFileName($uploadedFileName);
    }

    protected function cleanUploadedFileName(string $fileName): string
    {
        $name = pathinfo($fileName, PATHINFO_FILENAME);

        $name = str($name)
            // Remove common upload/result labels from the filename.
            ->replaceMatches('/\b(test|exam|scores|score|upload|uploaded|sheet)\b/i', '')

            // Remove standalone CA from the course name.
            // Example: "IUO-GST113 Use of Library, Study Skill and ICT CA"
            // becomes: "IUO-GST113 Use of Library, Study Skill and ICT"
            ->replaceMatches('/\bCA\b/i', '')

            // Clean spacing and trailing separators.
            ->replaceMatches('/\s+/', ' ')
            ->replaceMatches('/\s*-\s*$/', '')
            ->replaceMatches('/\s*,\s*$/', '')
            ->trim()
            ->toString();

        return $name !== '' ? $name : 'Merged Results';
    }
    protected function makeExportFileName(string $exportTitle): string
    {
        $fileName = "{$exportTitle} MERGE RESULT.xlsx";

        return str($fileName)
            ->replaceMatches('/[\/\\\\:*?"<>|]/', '')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }
}