<?php

namespace App\Services\Results;

use App\Enums\ResultIssueSeverity;
use App\Enums\ResultIssueStatus;
use App\Enums\ResultIssueType;
use App\Models\ExamScore;
use App\Models\ImportBatch;
use App\Models\ResultIssue;
use App\Models\TestScore;
use Illuminate\Support\Facades\DB;

class BatchScoreRevalidationService
{
    public function __construct(
        protected ManualScoreValidationService $manualScoreValidationService,
        protected IssueStateService $issueStateService,
    ) {
    }

    public function revalidateTestBatch(ImportBatch|int $batch): void
    {
        $batchId = $batch instanceof ImportBatch ? $batch->id : $batch;

        TestScore::query()
            ->where('import_batch_id', $batchId)
            ->orderBy('id')
            ->chunkById(500, function ($records): void {
                foreach ($records as $record) {
                    $data = $this->manualScoreValidationService
                        ->prepareTestScoreData($record->toArray(), $record->id);

                    $record->update([
                        'student_id' => $data['student_id'] ?? null,
                        'matric_no' => $data['matric_no'] ?? null,
                        'first_name' => $data['first_name'] ?? null,
                        'last_name' => $data['last_name'] ?? null,
                        'level' => $data['level'] ?? null,
                        'college' => $data['college'] ?? null,
                        'department' => $data['department'] ?? null,
                        'test_score' => $data['test_score'] ?? null,
                        'is_valid' => $data['is_valid'],
                        'validation_message' => $data['validation_message'],
                    ]);

                    $this->syncTestScoreIssueState($record->fresh());
                }
            });

        $this->refreshBatchCounts($batchId, 'test');
    }

    public function revalidateExamBatch(ImportBatch|int $batch): void
    {
        $batchId = $batch instanceof ImportBatch ? $batch->id : $batch;

        ExamScore::query()
            ->where('import_batch_id', $batchId)
            ->orderBy('id')
            ->chunkById(500, function ($records): void {
                foreach ($records as $record) {
                    $data = $this->manualScoreValidationService
                        ->prepareExamScoreData($record->toArray(), $record->id);

                    $record->update([
                        'student_id' => $data['student_id'] ?? null,
                        'matric_no' => $data['matric_no'] ?? null,
                        'first_name' => $data['first_name'] ?? null,
                        'last_name' => $data['last_name'] ?? null,
                        'level' => $data['level'] ?? null,
                        'college' => $data['college'] ?? null,
                        'department' => $data['department'] ?? null,
                        'exam_score' => $data['exam_score'] ?? null,
                        'is_valid' => $data['is_valid'],
                        'validation_message' => $data['validation_message'],
                    ]);

                    $this->syncExamScoreIssueState($record->fresh());
                }
            });

        $this->refreshBatchCounts($batchId, 'exam');
    }

    protected function syncTestScoreIssueState(TestScore $record): void
    {
        if ($record->is_valid) {
            $this->resolveOpenIssuesForTestScore($record);

            return;
        }

        $this->ensureOpenIssueForInvalidTestScore($record);
    }

    protected function syncExamScoreIssueState(ExamScore $record): void
    {
        if ($record->is_valid) {
            $this->resolveOpenIssuesForExamScore($record);

            return;
        }

        $this->ensureOpenIssueForInvalidExamScore($record);
    }

    protected function resolveOpenIssuesForTestScore(TestScore $record): void
    {
        ResultIssue::query()
            ->where('status', ResultIssueStatus::Open)
            ->where(function ($query) use ($record): void {
                $query->where('test_score_id', $record->id)
                    ->orWhere(function ($query) use ($record): void {
                        $query->where('import_batch_id', $record->import_batch_id);

                        $query->where(function ($query) use ($record): void {
                            if ($record->student_id) {
                                $query->orWhere('student_id', $record->student_id);
                            }

                            if ($record->matric_no) {
                                $query->orWhere('matric_no', $record->matric_no);
                            }
                        });
                    });
            })
            ->whereIn('type', [
                ResultIssueType::InvalidTestScore,
                ResultIssueType::DuplicateStudentId,
                ResultIssueType::DuplicateMatricNo,
                ResultIssueType::MissingStudentId,
            ])
            ->update([
                'status' => ResultIssueStatus::Resolved,
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);
    }

    protected function resolveOpenIssuesForExamScore(ExamScore $record): void
    {
        ResultIssue::query()
            ->where('status', ResultIssueStatus::Open)
            ->where(function ($query) use ($record): void {
                $query->where('exam_score_id', $record->id)
                    ->orWhere(function ($query) use ($record): void {
                        $query->where('import_batch_id', $record->import_batch_id);

                        $query->where(function ($query) use ($record): void {
                            if ($record->student_id) {
                                $query->orWhere('student_id', $record->student_id);
                            }

                            if ($record->matric_no) {
                                $query->orWhere('matric_no', $record->matric_no);
                            }
                        });
                    });
            })
            ->whereIn('type', [
                ResultIssueType::InvalidExamScore,
                ResultIssueType::DuplicateStudentId,
                ResultIssueType::DuplicateMatricNo,
                ResultIssueType::MissingStudentId,
            ])
            ->update([
                'status' => ResultIssueStatus::Resolved,
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);
    }

    protected function ensureOpenIssueForInvalidTestScore(TestScore $record): void
    {
        $type = $this->detectTestIssueType($record);

        $this->issueStateService->createOrUpdateOpenIssue(
            identity: [
                'type' => $type,
                'test_score_id' => $record->id,
            ],
            data: [
                'import_batch_id' => $record->import_batch_id,
                'severity' => ResultIssueSeverity::Error,
                'message' => $record->validation_message ?: 'Invalid test score record.',
                'student_id' => $record->student_id,
                'matric_no' => $record->matric_no,
                'level' => $record->level,
                'department' => $record->department,
                'row_number' => $record->row_number,
            ],
        );
    }

    protected function ensureOpenIssueForInvalidExamScore(ExamScore $record): void
    {
        $type = $this->detectExamIssueType($record);

        $this->issueStateService->createOrUpdateOpenIssue(
            identity: [
                'type' => $type,
                'exam_score_id' => $record->id,
            ],
            data: [
                'import_batch_id' => $record->import_batch_id,
                'severity' => ResultIssueSeverity::Error,
                'message' => $record->validation_message ?: 'Invalid exam score record.',
                'student_id' => $record->student_id,
                'matric_no' => $record->matric_no,
                'level' => $record->level,
                'department' => $record->department,
                'row_number' => $record->row_number,
            ],
        );
    }

    protected function detectTestIssueType(TestScore $record): ResultIssueType
    {
        $message = strtolower((string) $record->validation_message);

        if (str_contains($message, 'duplicate student')) {
            return ResultIssueType::DuplicateStudentId;
        }

        if (str_contains($message, 'duplicate matric')) {
            return ResultIssueType::DuplicateMatricNo;
        }

        if (str_contains($message, 'identifier') || str_contains($message, 'student id')) {
            return ResultIssueType::MissingStudentId;
        }

        return ResultIssueType::InvalidTestScore;
    }

    protected function detectExamIssueType(ExamScore $record): ResultIssueType
    {
        $message = strtolower((string) $record->validation_message);

        if (str_contains($message, 'duplicate student')) {
            return ResultIssueType::DuplicateStudentId;
        }

        if (str_contains($message, 'duplicate matric')) {
            return ResultIssueType::DuplicateMatricNo;
        }

        if (str_contains($message, 'identifier') || str_contains($message, 'student id')) {
            return ResultIssueType::MissingStudentId;
        }

        return ResultIssueType::InvalidExamScore;
    }

    protected function refreshBatchCounts(int $batchId, string $type): void
    {
        $table = $type === 'test' ? 'test_scores' : 'exam_scores';

        $totalRows = DB::table($table)
            ->where('import_batch_id', $batchId)
            ->count();

        $successfulRows = DB::table($table)
            ->where('import_batch_id', $batchId)
            ->where('is_valid', true)
            ->count();

        $failedRows = $totalRows - $successfulRows;

        $openIssues = ResultIssue::query()
            ->where('import_batch_id', $batchId)
            ->where('status', ResultIssueStatus::Open)
            ->count();

        DB::table('import_batches')
            ->where('id', $batchId)
            ->update([
                'total_rows' => $totalRows,
                'processed_rows' => $totalRows,
                'successful_rows' => $successfulRows,
                'failed_rows' => $failedRows,
                'issue_count' => $openIssues,
                'updated_at' => now(),
            ]);
    }
}