<?php

namespace Tests\Feature\ResultMerger;

use App\Models\GradeGuide;
use App\Models\GradingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

abstract class ResultMergerTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedDefaultGradingData();
    }

    protected function actingAsPanelUser(): User
    {
        $user = User::query()->create([
            'name' => 'Test Admin',
            'email' => 'admin-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        return $user;
    }

    protected function seedDefaultGradingData(): void
    {
        GradingSetting::query()->create([
            'name' => 'Default 40/60',
            'test_max' => 40,
            'exam_max' => 60,
            'total_max' => 100,
            'is_active' => true,
        ]);

        GradeGuide::query()->insert([
            [
                'minimum_score' => 70,
                'maximum_score' => 100,
                'grade' => 'A',
                'remark' => 'Excellent',
                'grade_point' => 5.00,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'minimum_score' => 60,
                'maximum_score' => 69,
                'grade' => 'B',
                'remark' => 'Very Good',
                'grade_point' => 4.00,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'minimum_score' => 50,
                'maximum_score' => 59,
                'grade' => 'C',
                'remark' => 'Good',
                'grade_point' => 3.00,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'minimum_score' => 45,
                'maximum_score' => 49,
                'grade' => 'D',
                'remark' => 'Fair',
                'grade_point' => 2.00,
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'minimum_score' => 40,
                'maximum_score' => 44,
                'grade' => 'E',
                'remark' => 'Pass',
                'grade_point' => 1.00,
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'minimum_score' => 0,
                'maximum_score' => 39,
                'grade' => 'F',
                'remark' => 'Fail',
                'grade_point' => 0.00,
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}