<?php

namespace App\Services\Results;

use Illuminate\Support\Facades\DB;

class ManualScoreValidationService
{
    public function __construct(
        protected ResultValidationService $validationService
    ) {
    }

    public function prepareTestScoreData(array $data, ?int $ignoreId = null): array
    {
        $data = $this->normalizeCommonData($data);

        $messages = [];

        $identifierValidation = $this->validationService->validateRequiredStudentIdentifier(
            $data['student_id'] ?? null,
            $data['matric_no'] ?? null,
        );

        if (!$identifierValidation['valid']) {
            $messages[] = $identifierValidation['message'];
        }

        $scoreValidation = $this->validationService->validateTestScore($data['test_score'] ?? null);

        if (!$scoreValidation['valid']) {
            $messages[] = $scoreValidation['message'];
        }

        $duplicateMessages = $this->detectDuplicates(
            table: 'test_scores',
            batchId: (int) $data['import_batch_id'],
            studentId: $data['student_id'] ?? null,
            matricNo: $data['matric_no'] ?? null,
            ignoreId: $ignoreId,
        );

        $messages = array_merge($messages, $duplicateMessages);

        $data['test_score'] = is_numeric($data['test_score'] ?? null)
            ? (float) $data['test_score']
            : null;

        $data['is_valid'] = empty($messages);
        $data['validation_message'] = empty($messages) ? null : implode(' ', $messages);

        return $data;
    }

    public function prepareExamScoreData(array $data, ?int $ignoreId = null): array
    {
        $data = $this->normalizeCommonData($data);

        $messages = [];

        $identifierValidation = $this->validationService->validateRequiredStudentIdentifier(
            $data['student_id'] ?? null,
            $data['matric_no'] ?? null,
        );

        if (!$identifierValidation['valid']) {
            $messages[] = $identifierValidation['message'];
        }

        $scoreValidation = $this->validationService->validateExamScore($data['exam_score'] ?? null);

        if (!$scoreValidation['valid']) {
            $messages[] = $scoreValidation['message'];
        }

        $duplicateMessages = $this->detectDuplicates(
            table: 'exam_scores',
            batchId: (int) $data['import_batch_id'],
            studentId: $data['student_id'] ?? null,
            matricNo: $data['matric_no'] ?? null,
            ignoreId: $ignoreId,
        );

        $messages = array_merge($messages, $duplicateMessages);

        $data['exam_score'] = is_numeric($data['exam_score'] ?? null)
            ? (float) $data['exam_score']
            : null;

        $data['is_valid'] = empty($messages);
        $data['validation_message'] = empty($messages) ? null : implode(' ', $messages);

        return $data;
    }

    protected function normalizeCommonData(array $data): array
    {
        foreach ([
            'student_id',
            'matric_no',
            'first_name',
            'last_name',
            'level',
            'college',
            'department',
        ] as $key) {
            $data[$key] = isset($data[$key]) && trim((string) $data[$key]) !== ''
                ? trim((string) $data[$key])
                : null;
        }

        if ($data['matric_no'] ?? null) {
            $data['matric_no'] = strtoupper($data['matric_no']);
        }

        if ($data['level'] ?? null) {
            $data['level'] = strtoupper($data['level']);
        }

        return $data;
    }

    protected function detectDuplicates(
        string $table,
        int $batchId,
        ?string $studentId,
        ?string $matricNo,
        ?int $ignoreId = null
    ): array {
        $messages = [];

        if ($studentId) {
            $exists = DB::table($table)
                ->where('import_batch_id', $batchId)
                ->where('student_id', $studentId)
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->exists();

            if ($exists) {
                $messages[] = 'Duplicate student ID found in this batch.';
            }
        }

        if ($matricNo) {
            $exists = DB::table($table)
                ->where('import_batch_id', $batchId)
                ->where('matric_no', $matricNo)
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->exists();

            if ($exists) {
                $messages[] = 'Duplicate matric number found in this batch.';
            }
        }

        return $messages;
    }
}