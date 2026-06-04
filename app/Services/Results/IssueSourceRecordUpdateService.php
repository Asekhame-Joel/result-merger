<?php

namespace App\Services\Results;

use App\Models\ResultIssue;
use Illuminate\Support\Facades\DB;

class IssueSourceRecordUpdateService
{
    public function __construct(
        protected ManualScoreValidationService $manualScoreValidationService,
        protected BatchScoreRevalidationService $batchScoreRevalidationService,
    ) {
    }

    public function updateFromIssue(ResultIssue $issue, array $data, ?int $resolvedBy = null): void
    {
        DB::transaction(function () use ($issue, $data, $resolvedBy): void {
            if ($issue->testScore) {
                $this->updateTestScore($issue, $data, $resolvedBy);

                return;
            }

            if ($issue->examScore) {
                $this->updateExamScore($issue, $data, $resolvedBy);
            }
        });
    }

    protected function updateTestScore(ResultIssue $issue, array $data, ?int $resolvedBy = null): void
    {
        $testScore = $issue->testScore;

        $payload = [
            'import_batch_id' => $testScore->import_batch_id,
            'student_id' => $data['student_id'] ?? null,
            'matric_no' => $data['matric_no'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'level' => $data['level'] ?? null,
            'college' => $data['college'] ?? null,
            'department' => $data['department'] ?? null,
            'test_score' => $data['score'] ?? null,
            'row_number' => $testScore->row_number,
        ];

        $payload = $this->manualScoreValidationService
            ->prepareTestScoreData($payload, $testScore->id);

        $testScore->update([
            'student_id' => $payload['student_id'] ?? null,
            'matric_no' => $payload['matric_no'] ?? null,
            'first_name' => $payload['first_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'level' => $payload['level'] ?? null,
            'college' => $payload['college'] ?? null,
            'department' => $payload['department'] ?? null,
            'test_score' => $payload['test_score'] ?? null,
            'is_valid' => $payload['is_valid'],
            'validation_message' => $payload['validation_message'],
        ]);

        $issue->update([
            'student_id' => $payload['student_id'] ?? null,
            'matric_no' => $payload['matric_no'] ?? null,
            'level' => $payload['level'] ?? null,
            'department' => $payload['department'] ?? null,
            'message' => $payload['validation_message'] ?: 'Source test score record updated.',
        ]);

        $this->batchScoreRevalidationService
            ->revalidateTestBatch($testScore->import_batch_id);

        if ((bool) ($data['resolve_after_save'] ?? true)) {
            $issue->resolve($resolvedBy);
        }
    }

    protected function updateExamScore(ResultIssue $issue, array $data, ?int $resolvedBy = null): void
    {
        $examScore = $issue->examScore;

        $payload = [
            'import_batch_id' => $examScore->import_batch_id,
            'student_id' => $data['student_id'] ?? null,
            'matric_no' => $data['matric_no'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'level' => $data['level'] ?? null,
            'college' => $data['college'] ?? null,
            'department' => $data['department'] ?? null,
            'exam_score' => $data['score'] ?? null,
            'row_number' => $examScore->row_number,
        ];

        $payload = $this->manualScoreValidationService
            ->prepareExamScoreData($payload, $examScore->id);

        $examScore->update([
            'student_id' => $payload['student_id'] ?? null,
            'matric_no' => $payload['matric_no'] ?? null,
            'first_name' => $payload['first_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'level' => $payload['level'] ?? null,
            'college' => $payload['college'] ?? null,
            'department' => $payload['department'] ?? null,
            'exam_score' => $payload['exam_score'] ?? null,
            'is_valid' => $payload['is_valid'],
            'validation_message' => $payload['validation_message'],
        ]);

        $issue->update([
            'student_id' => $payload['student_id'] ?? null,
            'matric_no' => $payload['matric_no'] ?? null,
            'level' => $payload['level'] ?? null,
            'department' => $payload['department'] ?? null,
            'message' => $payload['validation_message'] ?: 'Source exam score record updated.',
        ]);

        $this->batchScoreRevalidationService
            ->revalidateExamBatch($examScore->import_batch_id);

        if ((bool) ($data['resolve_after_save'] ?? true)) {
            $issue->resolve($resolvedBy);
        }
    }
}