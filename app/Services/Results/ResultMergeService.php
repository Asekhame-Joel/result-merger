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

    public function merge(
        ImportBatch $mergeBatch,
        ImportBatch $testBatch,
        ImportBatch $examBatch,
        string $matchBy = 'student_id'
    ): void {
        if (!in_array($matchBy, ['student_id', 'matric_no'], true)) {
            throw new RuntimeException('Invalid matching method selected.');
        }

        $validTestCount = TestScore::query()
            ->where('import_batch_id', $testBatch->id)
            ->where('is_valid', true)
            ->count();

        $mergeBatch->update([
            'total_rows' => $validTestCount,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'issue_count' => 0,
        ]);

        if ($validTestCount === 0) {
            throw new RuntimeException('No valid test scores found in the selected test batch.');
        }

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

                        $issueRows[] = $this->issueRow(
                            mergeBatch: $mergeBatch,
                            testScore: $testScore,
                            examScore: null,
                            type: ResultIssueType::MissingExamRecord,
                            message: "No matching exam record found using {$matchBy}.",
                            now: $now
                        );

                        continue;
                    }

                    $testValue = (float) $testScore->test_score;
                    $examValue = (float) $examScore->exam_score;
                    $totalScore = $testValue + $examValue;

                    $totalValidation = $this->validationService->validateTotalScore($totalScore);
                    $grade = $this->gradeResolver->resolve($totalScore);

                    $isValid = $totalValidation['valid'];

                    $mergedRows[] = [
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

                        'is_valid' => $isValid,
                        'validation_message' => $isValid ? null : $totalValidation['message'],

                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if (!$totalValidation['valid']) {
                        $issueRows[] = $this->issueRow(
                            mergeBatch: $mergeBatch,
                            testScore: $testScore,
                            examScore: $examScore,
                            type: ResultIssueType::InvalidTotalScore,
                            message: $totalValidation['message'],
                            now: $now
                        );
                    }
                }

                DB::transaction(function () use ($mergedRows, $issueRows, $mergeBatch): void {
                    if ($mergedRows !== []) {
                        DB::table('merged_results')->insert($mergedRows);
                    }

                    if ($issueRows !== []) {
                        DB::table('result_issues')->insert($issueRows);
                    }

                    $mergeBatch->increment('processed_rows', count($mergedRows));
                });
            });

        $this->createIssuesForExamRecordsWithoutTest(
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

    protected function mergedRowWithoutExam(
        ImportBatch $mergeBatch,
        ImportBatch $testBatch,
        ImportBatch $examBatch,
        TestScore $testScore,
        mixed $now
    ): array {
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

            'test_score' => $testScore->test_score,
            'exam_score' => null,
            'total_score' => null,

            'grade' => null,
            'remark' => null,
            'grade_point' => null,

            'is_valid' => false,
            'validation_message' => 'No matching exam record found.',

            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    protected function createIssuesForExamRecordsWithoutTest(
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
            ->chunkById(500, function ($examScores) use ($mergeBatch, $matchBy): void {
                $now = now();

                $issues = $examScores->map(fn(ExamScore $examScore): array => $this->issueRow(
                    mergeBatch: $mergeBatch,
                    testScore: null,
                    examScore: $examScore,
                    type: ResultIssueType::MissingTestRecord,
                    message: "No matching test record found using {$matchBy}.",
                    now: $now
                ))->all();

                if ($issues !== []) {
                    DB::table('result_issues')->insert($issues);
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
                ->count(),
        ]);
    }
}