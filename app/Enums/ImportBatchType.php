<?php

namespace App\Enums;

enum ImportBatchType: string
{
    case Test = 'test';
    case Exam = 'exam';
    case Merge = 'merge';
    case Export = 'export';

    public function label(): string
    {
        return match ($this) {
            self::Test => 'Test Upload',
            self::Exam => 'Exam Upload',
            self::Merge => 'Result Merge',
            self::Export => 'Result Export',
        };
    }
}