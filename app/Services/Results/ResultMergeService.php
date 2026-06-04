<?php

namespace App\Services\Results;

use App\Enums\ResultIssueSeverity;
use App\Enums\ResultIssueType;
use App\Models\ExamScore;
use App\Models\ImportBatch;
use App\Models\MergedResult;
use App\Models\TestScore;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ResultMergeService
{
    public function __construct(
        protected ResultValidationService $validationService,
        protected GradeResolver $gradeResolver,
    ) {
    }

    protected function insertIssueRows(array $issueRows): void
    {
        $issueRows = app(IssueStateService::class)
            ->filterIssueRowsPreservingResolved($issueRows);

        if ($issueRows !== []) {
            DB::table('result_issues')->insert($issueRows);
        }
    }

    public function merge(
        ImportBatch $mergeBatch,
        ImportBatch $testBatch,
        ImportBatch $examBatch,
        string $matchBy = 'student_id'
    ): void {
        app(BatchScoreRevalidationService::class)->revalidateTestBatch($testBatch);
        app(BatchScoreRevalidationService::class)->revalidateExamBatch($examBatch);

        if (!in_array($matchBy, ['student_id', 'matric_no'], true)) {
            throw new RuntimeException('Invalid matching method selected.');
        }

        $validTestCount = TestScore::query()
            ->where('import_batch_id', $testBatch->id)
            ->where('is_valid', true)
            ->count();

        $validExamCount = ExamScore::query()
            ->where('import_batch_id', $examBatch->id)
            ->where('is_valid', true)
            ->count();

        if ($validTestCount === 0 && $validExamCount === 0) {
            throw new RuntimeException('No valid test or exam scores found in the selected batches.');
        }

        $mergeBatch->update([
            'total_rows' => $validTestCount,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'issue_count' => 0,
        ]);

        MergedResult::query()
            ->where('merge_batch_id', $mergeBatch->id)
            ->delete();

        TestScore::query()
            ->where('import_batch_id', $testBatch->id)
            ->where('is_valid', true)
            ->orderBy('id')
            ->chunkById(500, function ($testScores) use ($mergeBatch, $testBatch, $examBatch, $matchBy): void {
                $mergedRows = [];
                $issueRows = [];
                $now = now();

                $examLookup = $this->examLookupForChunk($examBatch, $testScores, $matchBy);

                foreach ($testScores as $testScore) {
                    $matchValue = $testScore->{$matchBy};

                    $examScore = $matchValue
                        ? ($examLookup[$matchValue] ?? null)
                        : null;

                    if (!$examScore) {
                        $mergedRows[] = $this->mergedRowWithoutExam(
                            mergeBatch: $mergeBatch,
                            testBatch: $testBatch,
                            examBatch: $examBatch,
                            testScore: $testScore,
                            now: $now
                        );

                        continue;
                    }

                    $mergedRow = $this->mergedRowWithTestAndExam(
                        mergeBatch: $mergeBatch,
                        testBatch: $testBatch,
                        examBatch: $examBatch,
                        testScore: $testScore,
                        examScore: $examScore,
                        now: $now
                    );

                    $mergedRows[] = $mergedRow;

                    if (!$mergedRow['is_valid']) {
                        $issueRows[] = $this->issueRow(
                            mergeBatch: $mergeBatch,
                            testScore: $testScore,
                            examScore: $examScore,
                            type: ResultIssueType::InvalidTotalScore,
                            message: $mergedRow['validation_message'] ?: 'Invalid total score.',
                            now: $now
                        );
                    }
                }

                DB::transaction(function () use ($mergedRows, $issueRows, $mergeBatch): void {
                    if ($mergedRows !== []) {
                        DB::table('merged_results')->insert($mergedRows);
                    }

                    if ($issueRows !== []) {
                        $this->insertIssueRows($issueRows);
                    }

                    $mergeBatch->increment('processed_rows', count($mergedRows));
                });
            });

        $this->createZeroTestMergedRowsForExamOnlyRecords(
            mergeBatch: $mergeBatch,
            testBatch: $testBatch,
            examBatch: $examBatch,
            matchBy: $matchBy
        );

        $this->finalizeBatchCounts($mergeBatch);
    }

    protected function examLookupForChunk(ImportBatch $examBatch, $testScores, string $matchBy): array
    {
        $values = $testScores
            ->pluck($matchBy)
            ->filter()
            ->unique()
            ->values();

        if ($values->isEmpty()) {
            return [];
        }

        return ExamScore::query()
            ->where('import_batch_id', $examBatch->id)
            ->where('is_valid', true)
            ->whereIn($matchBy, $values)
            ->get()
            ->keyBy($matchBy)
            ->all();
    }

    protected function mergedRowWithTestAndExam(
        ImportBatch $mergeBatch,
        ImportBatch $testBatch,
        ImportBatch $examBatch,
        TestScore $testScore,
        ExamScore $examScore,
        mixed $now
    ): array {
        $testValue = (float) $testScore->test_score;
        $examValue = (float) $examScore->exam_score;
        $totalScore = $testValue + $examValue;

        $totalValidation = $this->validationService->validateTotalScore($totalScore);
        $grade = $this->gradeResolver->resolve($totalScore);

        return [
            'test_score_id' => $testScore->id,
            'exam_score_id' => $examScore->id,
            'test_import_batch_id' => $testBatch->id,
            'exam_import_batch_id' => $examBatch->id,
            'merge_batch_id' => $mergeBatch->id,

            'student_id' => $testScore->student_id ?: $examScore->student_id,
            'matric_no' => $testScore->matric_no ?: $examScore->matric_no,
            'first_name' => $testScore->first_name ?: $examScore->first_name,
            'last_name' => $testScore->last_name ?: $examScore->last_name,
            'level' => $testScore->level ?: $examScore->level,
            'college' => $testScore->college ?: $examScore->college,
            'department' => $testScore->department ?: $examScore->department,

            'test_score' => $testValue,
            'exam_score' => $examValue,
            'total_score' => $totalScore,

            'grade' => $grade['grade'],
            'remark' => $grade['remark'],
            'grade_point' => $grade['grade_point'],

            'is_valid' => $totalValidation['valid'],
            'validation_message' => $totalValidation['valid'] ? null : $totalValidation['message'],

            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    protected function mergedRowWithoutExam(
        ImportBatch $mergeBatch,
        ImportBatch $testBatch,
        ImportBatch $examBatch,
        TestScore $testScore,
        mixed $now
    ): array {
        $testScoreValue = (float) $testScore->test_score;
        $examScoreValue = 0.0;
        $totalScore = $testScoreValue + $examScoreValue;

        $totalValidation = $this->validationService->validateTotalScore($totalScore);
        $grade = $this->gradeResolver->resolve($totalScore);

        return [
            'test_score_id' => $testScore->id,
            'exam_score_id' => null,
            'test_import_batch_id' => $testBatch->id,
            'exam_import_batch_id' => $examBatch->id,
            'merge_batch_id' => $mergeBatch->id,

            'student_id' => $testScore->student_id,
            'matric_no' => $testScore->matric_no,
            'first_name' => $testScore->first_name,
            'last_name' => $testScore->last_name,
            'level' => $testScore->level,
            'college' => $testScore->college,
            'department' => $testScore->department,

            'test_score' => $testScoreValue,
            'exam_score' => $examScoreValue,
            'total_score' => $totalScore,

            'grade' => $grade['grade'],
            'remark' => $grade['remark'],
            'grade_point' => $grade['grade_point'],

            'is_valid' => $totalValidation['valid'],
            'validation_message' => $totalValidation['valid'] ? null : $totalValidation['message'],

            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    protected function mergedRowWithoutTest(
        ImportBatch $mergeBatch,
        ImportBatch $testBatch,
        ImportBatch $examBatch,
        ExamScore $examScore,
        mixed $now
    ): array {
        $testScoreValue = 0.0;
        $examScoreValue = (float) $examScore->exam_score;
        $totalScore = $testScoreValue + $examScoreValue;

        $totalValidation = $this->validationService->validateTotalScore($totalScore);
        $grade = $this->gradeResolver->resolve($totalScore);

        return [
            'test_score_id' => null,
            'exam_score_id' => $examScore->id,
            'test_import_batch_id' => $testBatch->id,
            'exam_import_batch_id' => $examBatch->id,
            'merge_batch_id' => $mergeBatch->id,

            'student_id' => $examScore->student_id,
            'matric_no' => $examScore->matric_no,
            'first_name' => $examScore->first_name,
            'last_name' => $examScore->last_name,
            'level' => $examScore->level,
            'college' => $examScore->college,
            'department' => $examScore->department,

            'test_score' => $testScoreValue,
            'exam_score' => $examScoreValue,
            'total_score' => $totalScore,

            'grade' => $grade['grade'],
            'remark' => $grade['remark'],
            'grade_point' => $grade['grade_point'],

            'is_valid' => $totalValidation['valid'],
            'validation_message' => $totalValidation['valid'] ? null : $totalValidation['message'],

            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    protected function createZeroTestMergedRowsForExamOnlyRecords(
        ImportBatch $mergeBatch,
        ImportBatch $testBatch,
        ImportBatch $examBatch,
        string $matchBy
    ): void {
        $testValues = TestScore::query()
            ->where('import_batch_id', $testBatch->id)
            ->where('is_valid', true)
            ->whereNotNull($matchBy)
            ->pluck($matchBy)
            ->filter()
            ->unique();

        ExamScore::query()
            ->where('import_batch_id', $examBatch->id)
            ->where('is_valid', true)
            ->whereNotNull($matchBy)
            ->whereNotIn($matchBy, $testValues)
            ->orderBy('id')
            ->chunkById(500, function ($examScores) use ($mergeBatch, $testBatch, $examBatch): void {
                $now = now();

                $mergedRows = [];

                foreach ($examScores as $examScore) {
                    $mergedRows[] = $this->mergedRowWithoutTest(
                        mergeBatch: $mergeBatch,
                        testBatch: $testBatch,
                        examBatch: $examBatch,
                        examScore: $examScore,
                        now: $now
                    );
                }

                if ($mergedRows !== []) {
                    DB::table('merged_results')->insert($mergedRows);
                    $mergeBatch->increment('processed_rows', count($mergedRows));
                }
            });
    }

    protected function issueRow(
        ImportBatch $mergeBatch,
        ?TestScore $testScore,
        ?ExamScore $examScore,
        ResultIssueType $type,
        string $message,
        mixed $now
    ): array {
        return [
            'import_batch_id' => $mergeBatch->id,
            'merged_result_id' => null,
            'test_score_id' => $testScore?->id,
            'exam_score_id' => $examScore?->id,
            'type' => $type->value,
            'severity' => ResultIssueSeverity::Error->value,
            'status' => 'open',
            'message' => $message,
            'row_number' => $testScore?->row_number ?? $examScore?->row_number,
            'student_id' => $testScore?->student_id ?? $examScore?->student_id,
            'matric_no' => $testScore?->matric_no ?? $examScore?->matric_no,
            'level' => $testScore?->level ?? $examScore?->level,
            'department' => $testScore?->department ?? $examScore?->department,
            'metadata' => null,
            'resolved_at' => null,
            'resolved_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    protected function finalizeBatchCounts(ImportBatch $mergeBatch): void
    {
        $mergeBatch->update([
            'total_rows' => DB::table('merged_results')
                ->where('merge_batch_id', $mergeBatch->id)
                ->count(),

            'processed_rows' => DB::table('merged_results')
                ->where('merge_batch_id', $mergeBatch->id)
                ->count(),

            'successful_rows' => DB::table('merged_results')
                ->where('merge_batch_id', $mergeBatch->id)
                ->where('is_valid', true)
                ->count(),

            'failed_rows' => DB::table('merged_results')
                ->where('merge_batch_id', $mergeBatch->id)
                ->where('is_valid', false)
                ->count(),

            'issue_count' => DB::table('result_issues')
                ->where('import_batch_id', $mergeBatch->id)
                ->where('status', 'open')
                ->count(),
        ]);
    }
}