<?php

namespace App\Services\Imports;

use Illuminate\Support\Str;

class ImportHeaderMapper
{
    public function normalizeHeader(string $header): string
    {
        return Str::of($header)
            ->trim()
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->replaceMatches('/[^a-z0-9\s_\/\-\(\)%:]/', '')
            ->trim()
            ->toString();
    }

    public function mapHeader(string $header, string $scoreType): ?string
    {
        $header = $this->normalizeHeader($header);

        $directMap = [
            'first name' => 'first_name',
            'firstname' => 'first_name',
            'first_name' => 'first_name',
            'given name' => 'first_name',

            'last name' => 'last_name',
            'lastname' => 'last_name',
            'last_name' => 'last_name',
            'surname' => 'last_name',
            'family name' => 'last_name',

            'student id' => 'student_id',
            'student_id' => 'student_id',
            'studentid' => 'student_id',
            'student number' => 'student_id',
            'student no' => 'student_id',
            'registration number' => 'student_id',
            'reg number' => 'student_id',
            'reg no' => 'student_id',

            'id number' => 'matric_no',
            'id no' => 'matric_no',
            'mat number' => 'matric_no',
            'mat no' => 'matric_no',
            'matric number' => 'matric_no',
            'matric no' => 'matric_no',
            'matric_no' => 'matric_no',
            'matriculation number' => 'matric_no',

            'institution' => 'college',
            'college' => 'college',
            'faculty' => 'college',
            'school' => 'college',

            'department' => 'department',
            'dept' => 'department',

            'level' => 'level',
            'class' => 'level',

            'test_score' => 'test_score',
            'test score' => 'test_score',
            'exam_score' => 'exam_score',
            'exam score' => 'exam_score',
        ];

        if (array_key_exists($header, $directMap)) {
            return $directMap[$header];
        }

        if ($this->isScoreHeader($header)) {
            return $scoreType === 'test'
                ? 'test_score'
                : 'exam_score';
        }

        return null;
    }

    public function normalizeHeaders(array $headers, string $scoreType): array
    {
        return collect($headers)
            ->map(function ($header) use ($scoreType): string {
                $mappedHeader = $this->mapHeader((string) $header, $scoreType);

                return $mappedHeader ?: '';
            })
            ->all();
    }

    protected function isScoreHeader(string $header): bool
    {
        /*
         * Handles headers like:
         * Quiz: IUO-GST113 Use of Library, Study Skill and ICT (Real)
         * Quiz
         * Test
         * Exam
         * Score
         * Total
         * Real
         */
        return str_contains($header, 'quiz')
            || str_contains($header, 'test')
            || str_contains($header, 'exam')
            || str_contains($header, 'score')
            || str_contains($header, 'total')
            || str_contains($header, 'real');
    }
}