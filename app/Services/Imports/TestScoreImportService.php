<?php

namespace App\Services\Imports;

use App\Enums\ResultIssueSeverity;
use App\Enums\ResultIssueType;
use App\Models\ImportBatch;
use App\Models\TestScore;
use App\Services\Results\ResultValidationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TestScoreImportService
{
    public function __construct(
        protected ResultValidationService $validationService
    ) {
    }

    public function importRows(ImportBatch $batch, Collection $rows): void
    {
        $totalRows = $rows->count();

        $batch->update([
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'issue_count' => 0,
        ]);

        if ($rows->isEmpty()) {
            throw new RuntimeException('No test score rows found. Confirm the Excel file contains student_id or matric_no and test_score headers.');
        }

        $now = now();

        $scoreRows = [];
        $issueRows = [];

        foreach ($rows as $index => $row) {
            $row = collect($row);
            $rowNumber = $index + 2;

            $studentId = $this->clean($this->value($row, ['student_id', 'student id', 'studentid']));
            $matricNo = $this->upper($this->value($row, ['matric_no', 'matric no', 'matric', 'matric_number', 'matric number']));

            $firstName = $this->clean($this->value($row, ['first_name', 'first name', 'firstname']));
            $lastName = $this->clean($this->value($row, ['last_name', 'last name', 'lastname']));

            $level = $this->upper($this->value($row, ['level', 'Level']));
            $college = $this->clean($this->value($row, ['college']));
            $department = $this->clean($this->value($row, ['department']));

            $testScoreValue = $this->value($row, ['test_score', 'test score']);

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

            $scoreRows[] = [
                'import_batch_id' => $batch->id,
                'student_id' => $studentId,
                'matric_no' => $matricNo,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'level' => $level,
                'college' => $college,
                'department' => $department,
                'test_score' => is_numeric($testScoreValue) ? (float) $testScoreValue : null,
                'row_number' => $rowNumber,
                'is_valid' => $isValid,
                'validation_message' => $isValid ? null : implode(' ', $messages),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (!$identifierValidation['valid']) {
                $issueRows[] = $this->issueRow(
                    batch: $batch,
                    type: ResultIssueType::MissingStudentId,
                    message: $identifierValidation['message'],
                    rowNumber: $rowNumber,
                    studentId: $studentId,
                    matricNo: $matricNo,
                    level: $level,
                    department: $department,
                    now: $now
                );
            }

            if (!$scoreValidation['valid']) {
                $issueRows[] = $this->issueRow(
                    batch: $batch,
                    type: ResultIssueType::InvalidTestScore,
                    message: $scoreValidation['message'],
                    rowNumber: $rowNumber,
                    studentId: $studentId,
                    matricNo: $matricNo,
                    level: $level,
                    department: $department,
                    now: $now
                );
            }
        }

        DB::transaction(function () use ($batch, $scoreRows, $issueRows): void {
            foreach (array_chunk($scoreRows, 500) as $chunk) {
                TestScore::query()->insert($chunk);

                $batch->increment('processed_rows', count($chunk));
            }

            foreach (array_chunk($issueRows, 500) as $chunk) {
                DB::table('result_issues')->insert($chunk);
            }
        });

        $this->detectDuplicateStudentIds($batch);
        $this->detectDuplicateMatricNumbers($batch);

        $this->finalizeBatchCounts($batch);
    }

    protected function detectDuplicateStudentIds(ImportBatch $batch): void
    {
        $duplicates = TestScore::query()
            ->select('student_id')
            ->where('import_batch_id', $batch->id)
            ->whereNotNull('student_id')
            ->where('student_id', '!=', '')
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('student_id');

        if ($duplicates->isEmpty()) {
            return;
        }

        TestScore::query()
            ->where('import_batch_id', $batch->id)
            ->whereIn('student_id', $duplicates)
            ->update([
                'is_valid' => false,
                'validation_message' => DB::raw("TRIM(CONCAT(COALESCE(validation_message, ''), ' Duplicate student ID found in this batch.'))"),
                'updated_at' => now(),
            ]);

        $this->createDuplicateIssues(
            batch: $batch,
            column: 'student_id',
            values: $duplicates,
            type: ResultIssueType::DuplicateStudentId,
            message: 'Duplicate student ID found in this test score batch.'
        );
    }

    protected function detectDuplicateMatricNumbers(ImportBatch $batch): void
    {
        $duplicates = TestScore::query()
            ->select('matric_no')
            ->where('import_batch_id', $batch->id)
            ->whereNotNull('matric_no')
            ->where('matric_no', '!=', '')
            ->groupBy('matric_no')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('matric_no');

        if ($duplicates->isEmpty()) {
            return;
        }

        TestScore::query()
            ->where('import_batch_id', $batch->id)
            ->whereIn('matric_no', $duplicates)
            ->update([
                'is_valid' => false,
                'validation_message' => DB::raw("TRIM(CONCAT(COALESCE(validation_message, ''), ' Duplicate matric number found in this batch.'))"),
                'updated_at' => now(),
            ]);

        $this->createDuplicateIssues(
            batch: $batch,
            column: 'matric_no',
            values: $duplicates,
            type: ResultIssueType::DuplicateMatricNo,
            message: 'Duplicate matric number found in this test score batch.'
        );
    }

    protected function createDuplicateIssues(
        ImportBatch $batch,
        string $column,
        Collection $values,
        ResultIssueType $type,
        string $message
    ): void {
        $now = now();

        TestScore::query()
            ->where('import_batch_id', $batch->id)
            ->whereIn($column, $values)
            ->orderBy('id')
            ->chunkById(500, function (Collection $scores) use ($batch, $type, $message, $now): void {
                $issues = $scores->map(fn(TestScore $score): array => [
                    'import_batch_id' => $batch->id,
                    'test_score_id' => $score->id,
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
                ])->all();

                DB::table('result_issues')->insert($issues);
            });
    }

    protected function finalizeBatchCounts(ImportBatch $batch): void
    {
        $batch->update([
            'processed_rows' => $batch->testScores()->count(),
            'successful_rows' => $batch->testScores()->where('is_valid', true)->count(),
            'failed_rows' => $batch->testScores()->where('is_valid', false)->count(),
            'issue_count' => $batch->issues()->count(),
        ]);
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

    protected function value(Collection $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            $normalizedKey = Str::of($key)
                ->lower()
                ->replace([' ', '-', '.', '/', '\\'], '_')
                ->replaceMatches('/_+/', '_')
                ->trim('_')
                ->toString();

            if ($row->has($normalizedKey)) {
                return $row->get($normalizedKey);
            }

            if ($row->has($key)) {
                return $row->get($key);
            }
        }

        return null;
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