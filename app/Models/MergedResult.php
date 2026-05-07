<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MergedResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_score_id',
        'exam_score_id',
        'test_import_batch_id',
        'exam_import_batch_id',
        'merge_batch_id',
        'student_id',
        'matric_no',
        'first_name',
        'last_name',
        'level',
        'college',
        'department',
        'test_score',
        'exam_score',
        'total_score',
        'grade',
        'remark',
        'grade_point',
        'is_valid',
        'validation_message',
    ];

    protected function casts(): array
    {
        return [
            'test_score' => 'decimal:2',
            'exam_score' => 'decimal:2',
            'total_score' => 'decimal:2',
            'grade_point' => 'decimal:2',
            'is_valid' => 'boolean',
        ];
    }

    public function testScoreRecord(): BelongsTo
    {
        return $this->belongsTo(TestScore::class, 'test_score_id');
    }

    public function examScoreRecord(): BelongsTo
    {
        return $this->belongsTo(ExamScore::class, 'exam_score_id');
    }

    public function testImportBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'test_import_batch_id');
    }

    public function examImportBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'exam_import_batch_id');
    }

    public function mergeBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'merge_batch_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ResultIssue::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function setStudentIdAttribute(?string $value): void
    {
        $this->attributes['student_id'] = $value ? trim($value) : null;
    }

    public function setMatricNoAttribute(?string $value): void
    {
        $this->attributes['matric_no'] = $value ? strtoupper(trim($value)) : null;
    }

    public function setFirstNameAttribute(?string $value): void
    {
        $this->attributes['first_name'] = $value ? trim($value) : null;
    }

    public function setLastNameAttribute(?string $value): void
    {
        $this->attributes['last_name'] = $value ? trim($value) : null;
    }

    public function setLevelAttribute(?string $value): void
    {
        $this->attributes['level'] = $value ? strtoupper(trim($value)) : null;
    }

    public function setCollegeAttribute(?string $value): void
    {
        $this->attributes['college'] = $value ? trim($value) : null;
    }

    public function setDepartmentAttribute(?string $value): void
    {
        $this->attributes['department'] = $value ? trim($value) : null;
    }
}