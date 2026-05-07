<?php

namespace App\Services\Imports;

use App\Enums\ResultIssueSeverity;
use App\Enums\ResultIssueType;
use App\Models\ImportBatch;
use App\Services\Results\ResultValidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileObject;

class TestScoreCsvImportService
{
    public function __construct(
        protected ResultValidationService $validationService
    ) {
    }

    public function import(ImportBatch $batch): void
    {
        $path = Storage::disk($batch->disk)->path($batch->file_path);

        if (!file_exists($path)) {
            throw new RuntimeException('Uploaded CSV file could not be found.');
        }

        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

        $headers = null;
        $testScoreRows = [];
        $issueRows = [];

        $totalRows = 0;
        $processedRows = 0;
        $successfulRows = 0;
        $failedRows = 0;

        $now = now();

        foreach ($file as $rowIndex => $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if ($headers === null) {
                $headers = $this->normalizeHeaders($row);

                if (!$this->hasRequiredHeaders($headers)) {
                    throw new RuntimeException('Invalid CSV headers. Required headers include student_id or matric_no, and test_score.');
                }

                continue;
            }

            $row = $this->combineRow($headers, $row);

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $totalRows++;

            $studentId = $this->clean($row['student_id'] ?? null);
            $matricNo = $this->upper($row['matric_no'] ?? null);

            $firstName = $this->clean($row['first_name'] ?? null);
            $lastName = $this->clean($row['last_name'] ?? null);

            $level = $this->upper($row['level'] ?? null);
            $college = $this->clean($row['college'] ?? null);
            $department = $this->clean($row['department'] ?? null);

            $testScoreValue = $row['test_score'] ?? null;

            $messages = [];

            $identifierValidation = $this->validationService->validateRequiredStudentIdentifier($studentId, $matricNo);

            if (!$identifierValidation['valid']) {
                $messages[] = $identifierValidation['message'];
            }

            $scoreValidation = $this->validationService->validateTestScore($testScoreValue);

            if (!$scoreValidation['valid']) {
                $messages[] = $scoreValidation['message'];
            }

            $isValid = empty($messages);

            if ($isValid) {
                $successfulRows++;
            } else {
                $failedRows++;
            }

            $excelRowNumber = $rowIndex + 1;

            $testScoreRows[] = [
                'import_batch_id' => $batch->id,
                'student_id' => $studentId,
                'matric_no' => $matricNo,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'level' => $level,
                'college' => $college,
                'department' => $department,
                'test_score' => is_numeric($testScoreValue) ? (float) $testScoreValue : null,
                'row_number' => $excelRowNumber,
                'is_valid' => $isValid,
                'validation_message' => $isValid ? null : implode(' ', $messages),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (!$identifierValidation['valid']) {
                $issueRows[] = $this->issueRow(
                    $batch,
                    ResultIssueType::MissingStudentId,
                    $identifierValidation['message'],
                    $excelRowNumber,
                    $studentId,
                    $matricNo,
                    $level,
                    $department,
                    $now
                );
            }

            if (!$scoreValidation['valid']) {
                $issueRows[] = $this->issueRow(
                    $batch,
                    ResultIssueType::InvalidTestScore,
                    $scoreValidation['message'],
                    $excelRowNumber,
                    $studentId,
                    $matricNo,
                    $level,
                    $department,
                    $now
                );
            }

            if (count($testScoreRows) >= 500) {
                DB::table('test_scores')->insert($testScoreRows);

                if ($issueRows !== []) {
                    DB::table('result_issues')->insert($issueRows);
                }

                $processedRows += count($testScoreRows);

                $batch->update([
                    'total_rows' => $totalRows,
                    'processed_rows' => $processedRows,
                    'successful_rows' => $successfulRows,
                    'failed_rows' => $failedRows,
                    'issue_count' => DB::table('result_issues')->where('import_batch_id', $batch->id)->count(),
                ]);

                $testScoreRows = [];
                $issueRows = [];
            }
        }

        if ($headers === null) {
            throw new RuntimeException('CSV file is empty.');
        }

        if ($testScoreRows !== []) {
            DB::table('test_scores')->insert($testScoreRows);

            if ($issueRows !== []) {
                DB::table('result_issues')->insert($issueRows);
            }

            $processedRows += count($testScoreRows);
        }

        if ($totalRows === 0) {
            throw new RuntimeException('No student rows found in CSV file.');
        }

        $this->detectDuplicates($batch);

        $batch->update([
            'total_rows' => $totalRows,
            'processed_rows' => DB::table('test_scores')->where('import_batch_id', $batch->id)->count(),
            'successful_rows' => DB::table('test_scores')->where('import_batch_id', $batch->id)->where('is_valid', true)->count(),
            'failed_rows' => DB::table('test_scores')->where('import_batch_id', $batch->id)->where('is_valid', false)->count(),
            'issue_count' => DB::table('result_issues')->where('import_batch_id', $batch->id)->count(),
        ]);
    }

    protected function detectDuplicates(ImportBatch $batch): void
    {
        $this->detectDuplicateColumn(
            batch: $batch,
            column: 'student_id',
            type: ResultIssueType::DuplicateStudentId,
            message: 'Duplicate student ID found in this test score batch.'
        );

        $this->detectDuplicateColumn(
            batch: $batch,
            column: 'matric_no',
            type: ResultIssueType::DuplicateMatricNo,
            message: 'Duplicate matric number found in this test score batch.'
        );
    }

    protected function detectDuplicateColumn(ImportBatch $batch, string $column, ResultIssueType $type, string $message): void
    {
        $duplicates = DB::table('test_scores')
            ->select($column)
            ->where('import_batch_id', $batch->id)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->pluck($column);

        if ($duplicates->isEmpty()) {
            return;
        }

        DB::table('test_scores')
            ->where('import_batch_id', $batch->id)
            ->whereIn($column, $duplicates)
            ->update([
                'is_valid' => false,
                'validation_message' => DB::raw("TRIM(CONCAT(COALESCE(validation_message, ''), ' {$message}'))"),
                'updated_at' => now(),
            ]);

        $now = now();

        DB::table('test_scores')
            ->where('import_batch_id', $batch->id)
            ->whereIn($column, $duplicates)
            ->orderBy('id')
            ->chunkById(500, function ($scores) use ($batch, $type, $message, $now): void {
                $issues = [];

                foreach ($scores as $score) {
                    $issues[] = [
                        'import_batch_id' => $batch->id,
                        'merged_result_id' => null,
                        'test_score_id' => $score->id,
                        'exam_score_id' => null,
                        'type' => $type->value,
                        'severity' => ResultIssueSeverity::Error->value,
                        'status' => 'open',
                        'message' => $message,
                        'row_number' => $score->row_number,
                        'student_id' => $score->student_id,
                        'matric_no' => $score->matric_no,
                        'level' => $score->level,
                        'department' => $score->department,
                        'metadata' => null,
                        'resolved_at' => null,
                        'resolved_by' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($issues !== []) {
                    DB::table('result_issues')->insert($issues);
                }
            });
    }

    protected function normalizeHeaders(array $headers): array
    {
        return collect($headers)
            ->map(fn($header): string => Str::of((string) $header)
                ->trim()
                ->lower()
                ->replace([' ', '-', '.', '/', '\\'], '_')
                ->replaceMatches('/_+/', '_')
                ->trim('_')
                ->toString())
            ->all();
    }

    protected function hasRequiredHeaders(array $headers): bool
    {
        return (in_array('student_id', $headers, true) || in_array('matric_no', $headers, true))
            && in_array('test_score', $headers, true);
    }

    protected function combineRow(array $headers, array $row): array
    {
        $combined = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $combined[$header] = $row[$index] ?? null;
        }

        return $combined;
    }

    protected function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (filled($value)) {
                return false;
            }
        }

        return true;
    }

    protected function issueRow(
        ImportBatch $batch,
        ResultIssueType $type,
        string $message,
        int $rowNumber,
        ?string $studentId,
        ?string $matricNo,
        ?string $level,
        ?string $department,
        mixed $now
    ): array {
        return [
            'import_batch_id' => $batch->id,
            'merged_result_id' => null,
            'test_score_id' => null,
            'exam_score_id' => null,
            'type' => $type->value,
            'severity' => ResultIssueSeverity::Error->value,
            'status' => 'open',
            'message' => $message,
            'row_number' => $rowNumber,
            'student_id' => $studentId,
            'matric_no' => $matricNo,
            'level' => $level,
            'department' => $department,
            'metadata' => null,
            'resolved_at' => null,
            'resolved_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    protected function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function upper(mixed $value): ?string
    {
        $value = $this->clean($value);

        return $value ? strtoupper($value) : null;
    }
}