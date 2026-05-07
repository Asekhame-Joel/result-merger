<?php

namespace App\Services\Results;

use App\Models\GradeGuide;

class GradeResolver
{
    public function resolve(float|int $score): array
    {
        $guide = GradeGuide::findForScore($score);

        if (! $guide) {
            return [
                'grade' => null,
                'remark' => null,
                'grade_point' => null,
            ];
        }

        return [
            'grade' => $guide->grade,
            'remark' => $guide->remark,
            'grade_point' => $guide->grade_point,
        ];
    }
}