<?php

namespace Tests\Feature\ResultMerger;

use App\Models\GradeGuide;
use App\Models\GradingSetting;

class GradingSettingsTest extends ResultMergerTestCase
{
    public function test_only_one_grading_setting_can_be_active(): void
    {
        $first = GradingSetting::active();

        $second = GradingSetting::query()->create([
            'name' => 'Alternative 30/70',
            'test_max' => 30,
            'exam_max' => 70,
            'total_max' => 100,
            'is_active' => true,
        ]);

        $this->assertFalse($first->fresh()->is_active);
        $this->assertTrue($second->fresh()->is_active);
        $this->assertEquals($second->id, GradingSetting::active()->id);
    }

    public function test_grade_guide_resolves_score_correctly(): void
    {
        $guide = GradeGuide::findForScore(72);

        $this->assertNotNull($guide);
        $this->assertEquals('A', $guide->grade);
        $this->assertEquals('Excellent', $guide->remark);
    }

    public function test_grade_guide_returns_null_for_unmatched_score(): void
    {
        GradeGuide::query()->delete();

        $this->assertNull(GradeGuide::findForScore(72));
    }
}