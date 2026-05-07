<?php

namespace Database\Seeders;

use App\Models\GradeGuide;
use Illuminate\Database\Seeder;

class GradeGuideSeeder extends Seeder
{
    public function run(): void
    {
        $guides = [
            [
                'minimum_score' => 70,
                'maximum_score' => 100,
                'grade' => 'A',
                'remark' => 'Excellent',
                'grade_point' => 5.00,
                'sort_order' => 1,
            ],
            [
                'minimum_score' => 60,
                'maximum_score' => 69,
                'grade' => 'B',
                'remark' => 'Very Good',
                'grade_point' => 4.00,
                'sort_order' => 2,
            ],
            [
                'minimum_score' => 50,
                'maximum_score' => 59,
                'grade' => 'C',
                'remark' => 'Good',
                'grade_point' => 3.00,
                'sort_order' => 3,
            ],
            [
                'minimum_score' => 45,
                'maximum_score' => 49,
                'grade' => 'D',
                'remark' => 'Fair',
                'grade_point' => 2.00,
                'sort_order' => 4,
            ],
            [
                'minimum_score' => 40,
                'maximum_score' => 44,
                'grade' => 'E',
                'remark' => 'Pass',
                'grade_point' => 1.00,
                'sort_order' => 5,
            ],
            [
                'minimum_score' => 0,
                'maximum_score' => 39,
                'grade' => 'F',
                'remark' => 'Fail',
                'grade_point' => 0.00,
                'sort_order' => 6,
            ],
        ];

        foreach ($guides as $guide) {
            GradeGuide::query()->updateOrCreate(
                [
                    'minimum_score' => $guide['minimum_score'],
                    'maximum_score' => $guide['maximum_score'],
                    'grade' => $guide['grade'],
                ],
                [
                    'remark' => $guide['remark'],
                    'grade_point' => $guide['grade_point'],
                    'sort_order' => $guide['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}