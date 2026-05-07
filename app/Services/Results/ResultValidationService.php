<?php

namespace App\Services\Results;

use App\Models\GradingSetting;

class ResultValidationService
{
    public function validateTestScore(null|int|float|string $score): array
    {
        $setting = GradingSetting::active();

        if (!$setting) {
            return [
                'valid' => false,
                'message' => 'No active grading setting found.',
            ];
        }

        if ($score === null || $score === '') {
            return [
                'valid' => false,
                'message' => 'Test score is missing.',
            ];
        }

        if (!is_numeric($score)) {
            return [
                'valid' => false,
                'message' => 'Test score must be numeric.',
            ];
        }

        $score = (float) $score;

        if ($score < 0) {
            return [
                'valid' => false,
                'message' => 'Test score cannot be negative.',
            ];
        }

        if ($score > (float) $setting->test_max) {
            return [
                'valid' => false,
                'message' => "Test score cannot exceed {$setting->test_max}.",
            ];
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }

    public function validateExamScore(null|int|float|string $score): array
    {
        $setting = GradingSetting::active();

        if (!$setting) {
            return [
                'valid' => false,
                'message' => 'No active grading setting found.',
            ];
        }

        if ($score === null || $score === '') {
            return [
                'valid' => false,
                'message' => 'Exam score is missing.',
            ];
        }

        if (!is_numeric($score)) {
            return [
                'valid' => false,
                'message' => 'Exam score must be numeric.',
            ];
        }

        $score = (float) $score;

        if ($score < 0) {
            return [
                'valid' => false,
                'message' => 'Exam score cannot be negative.',
            ];
        }

        if ($score > (float) $setting->exam_max) {
            return [
                'valid' => false,
                'message' => "Exam score cannot exceed {$setting->exam_max}.",
            ];
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }

    public function validateTotalScore(null|int|float|string $score): array
    {
        $setting = GradingSetting::active();

        if (!$setting) {
            return [
                'valid' => false,
                'message' => 'No active grading setting found.',
            ];
        }

        if ($score === null || $score === '') {
            return [
                'valid' => false,
                'message' => 'Total score is missing.',
            ];
        }

        if (!is_numeric($score)) {
            return [
                'valid' => false,
                'message' => 'Total score must be numeric.',
            ];
        }

        $score = (float) $score;

        if ($score < 0) {
            return [
                'valid' => false,
                'message' => 'Total score cannot be negative.',
            ];
        }

        if ($score > (float) $setting->total_max) {
            return [
                'valid' => false,
                'message' => "Total score cannot exceed {$setting->total_max}.",
            ];
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }

    public function validateRequiredStudentIdentifier(?string $studentId, ?string $matricNo): array
    {
        if (blank($studentId) && blank($matricNo)) {
            return [
                'valid' => false,
                'message' => 'Either student ID or matric number is required.',
            ];
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }
}