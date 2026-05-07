<?php

namespace Database\Seeders;

use App\Models\GradingSetting;
use Illuminate\Database\Seeder;

class GradingSettingSeeder extends Seeder
{
    public function run(): void
    {
        GradingSetting::query()->update([
            'is_active' => false,
        ]);

        GradingSetting::query()->updateOrCreate(
            ['name' => 'Default 40/60 Grading Setting'],
            [
                'test_max' => 40,
                'exam_max' => 60,
                'total_max' => 100,
                'is_active' => true,
            ]
        );
    }
}