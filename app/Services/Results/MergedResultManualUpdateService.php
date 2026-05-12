<?php

namespace App\Services\Results;

use App\Models\GradeGuide;
use App\Models\MergedResult;
use Illuminate\Support\Facades\DB;

class MergedResultManualUpdateService
{
    public function updateScores(MergedResult $mergedResult, array $data): array
    {
        $testScore = $this->toNullableFloat($data['test_score'] ?? null);
        $examScore = $this->toNullableFloat($data['exam_score'] ?? null);

        $totalScore = null;

        if ($testScore !== null && $examScore !== null) {
            $totalScore = $testScore + $examScore;
        }

        $gradeGuide = $totalScore !== null
            ? GradeGuide::findForScore($totalScore)
            : null;

        return DB::transaction(function () use ($mergedResult, $testScore, $examScore, $totalScore, $gradeGuide): array {
            $mergedData = [
                'test_score' => $testScore,
                'exam_score' => $examScore,
                'total_score' => $totalScore,
                'grade' => $gradeGuide?->grade,
                'remark' => $gradeGuide?->remark,
                'grade_point' => $gradeGuide?->grade_point,
                'is_valid' => $testScore !== null && $examScore !== null && $totalScore !== null,
                'validation_message' => $testScore !== null && $examScore !== null
                    ? null
                    : 'Manually edited result has missing test or exam score.',
            ];

            $mergedResult->update($mergedData);

            /*
             * Keep linked source scores in sync when possible.
             * This helps if you later re-run merge.
             */
            if ($mergedResult->testScore && $testScore !== null) {
                $mergedResult->testScore->update([
                    'test_score' => $testScore,
                    'is_valid' => true,
                    'validation_message' => null,
                ]);
            }

            if ($mergedResult->examScore && $examScore !== null) {
                $mergedResult->examScore->update([
                    'exam_score' => $examScore,
                    'is_valid' => true,
                    'validation_message' => null,
                ]);
            }

            return $mergedData;
        });
    }

    protected function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}