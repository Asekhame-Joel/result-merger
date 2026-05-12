<?php

namespace App\Services\Results;

use App\Enums\ResultIssueStatus;
use App\Models\ResultIssue;
use Illuminate\Database\Eloquent\Builder;

class IssueStateService
{
    /**
     * Create issue only if the same issue does not already exist.
     *
     * Important behavior:
     * - If same issue is already resolved, do not recreate it.
     * - If same issue is already ignored, do not recreate it.
     * - If same issue is still open, update it instead of duplicating it.
     */
    public function createOrUpdateOpenIssue(array $identity, array $data): ?ResultIssue
    {
        $issue = $this->findMatchingIssue($identity);

        if (!$issue) {
            return ResultIssue::query()->create(array_merge($data, [
                'type' => $identity['type'],
                'test_score_id' => $identity['test_score_id'] ?? null,
                'exam_score_id' => $identity['exam_score_id'] ?? null,
                'student_id' => $data['student_id'] ?? ($identity['student_id'] ?? null),
                'matric_no' => $data['matric_no'] ?? ($identity['matric_no'] ?? null),
                'import_batch_id' => $data['import_batch_id'] ?? ($identity['import_batch_id'] ?? null),
                'status' => ResultIssueStatus::Open,
            ]));
        }

        if ($this->isClosed($issue)) {
            return null;
        }

        $issue->update(array_merge($data, [
            'status' => ResultIssueStatus::Open,
        ]));

        return $issue;
    }

    /**
     * Used before bulk inserting issue rows during merge.
     *
     * It removes issue rows that already exist as resolved or ignored.
     * It updates already-open matching issues instead of creating duplicates.
     */
    public function filterIssueRowsPreservingResolved(array $rows): array
    {
        $filteredRows = [];

        foreach ($rows as $row) {
            $identity = $this->identityFromRow($row);

            $issue = $this->findMatchingIssue($identity);

            if (!$issue) {
                $filteredRows[] = $row;

                continue;
            }

            if ($this->isClosed($issue)) {
                continue;
            }

            $issue->update([
                'import_batch_id' => $row['import_batch_id'] ?? $issue->import_batch_id,
                'message' => $row['message'] ?? $issue->message,
                'severity' => $row['severity'] ?? $issue->severity,
                'student_id' => $row['student_id'] ?? $issue->student_id,
                'matric_no' => $row['matric_no'] ?? $issue->matric_no,
                'level' => $row['level'] ?? $issue->level,
                'department' => $row['department'] ?? $issue->department,
                'row_number' => $row['row_number'] ?? $issue->row_number,
                'updated_at' => now(),
            ]);
        }

        return $filteredRows;
    }

    public function resolveOpenIssuesForDeletedTestScore(int $testScoreId): void
    {
        ResultIssue::query()
            ->where('test_score_id', $testScoreId)
            ->where('status', ResultIssueStatus::Open)
            ->update([
                'status' => ResultIssueStatus::Resolved,
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function resolveOpenIssuesForDeletedExamScore(int $examScoreId): void
    {
        ResultIssue::query()
            ->where('exam_score_id', $examScoreId)
            ->where('status', ResultIssueStatus::Open)
            ->update([
                'status' => ResultIssueStatus::Resolved,
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);
    }

    protected function identityFromRow(array $row): array
    {
        return [
            'type' => $row['type'] ?? null,
            'test_score_id' => $row['test_score_id'] ?? null,
            'exam_score_id' => $row['exam_score_id'] ?? null,
            'student_id' => $row['student_id'] ?? null,
            'matric_no' => $row['matric_no'] ?? null,
            'import_batch_id' => $row['import_batch_id'] ?? null,
        ];
    }

    protected function findMatchingIssue(array $identity): ?ResultIssue
    {
        return ResultIssue::query()
            ->where('type', $identity['type'])
            ->where(function (Builder $query) use ($identity): void {
                if (!empty($identity['test_score_id'])) {
                    $query->orWhere('test_score_id', $identity['test_score_id']);
                }

                if (!empty($identity['exam_score_id'])) {
                    $query->orWhere('exam_score_id', $identity['exam_score_id']);
                }

                if (!empty($identity['student_id'])) {
                    $query->orWhere(function (Builder $query) use ($identity): void {
                        $query->where('student_id', $identity['student_id']);

                        if (!empty($identity['matric_no'])) {
                            $query->orWhere('matric_no', $identity['matric_no']);
                        }
                    });
                }

                if (!empty($identity['matric_no'])) {
                    $query->orWhere('matric_no', $identity['matric_no']);
                }

                if (
                    empty($identity['test_score_id']) &&
                    empty($identity['exam_score_id']) &&
                    empty($identity['student_id']) &&
                    empty($identity['matric_no']) &&
                    !empty($identity['import_batch_id'])
                ) {
                    $query->orWhere('import_batch_id', $identity['import_batch_id']);
                }
            })
            ->first();
    }

    protected function isClosed(ResultIssue $issue): bool
    {
        return in_array($issue->status, [
            ResultIssueStatus::Resolved,
            ResultIssueStatus::Ignored,
        ], true);
    }
}