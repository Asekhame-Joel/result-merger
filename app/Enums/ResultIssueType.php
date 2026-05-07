<?php

namespace App\Enums;

enum ResultIssueType: string
{
    case InvalidTestScore = 'invalid_test_score';
    case InvalidExamScore = 'invalid_exam_score';
    case InvalidTotalScore = 'invalid_total_score';

    case DuplicateStudentId = 'duplicate_student_id';
    case DuplicateMatricNo = 'duplicate_matric_no';

    case MissingStudentId = 'missing_student_id';
    case MissingMatricNo = 'missing_matric_no';

    case MissingTestRecord = 'missing_test_record';
    case MissingExamRecord = 'missing_exam_record';

    case UnmatchedResult = 'unmatched_result';
    case ImportRowError = 'import_row_error';

    public function label(): string
    {
        return match ($this) {
            self::InvalidTestScore => 'Invalid Test Score',
            self::InvalidExamScore => 'Invalid Exam Score',
            self::InvalidTotalScore => 'Invalid Total Score',
            self::DuplicateStudentId => 'Duplicate Student ID',
            self::DuplicateMatricNo => 'Duplicate Matric Number',
            self::MissingStudentId => 'Missing Student ID',
            self::MissingMatricNo => 'Missing Matric Number',
            self::MissingTestRecord => 'Missing Test Record',
            self::MissingExamRecord => 'Missing Exam Record',
            self::UnmatchedResult => 'Unmatched Result',
            self::ImportRowError => 'Import Row Error',
        };
    }
}