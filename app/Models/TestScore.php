<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_batch_id',
        'student_id',
        'matric_no',
        'first_name',
        'last_name',
        'level',
        'college',
        'department',
        'test_score',
        'row_number',
        'is_valid',
        'validation_message',
    ];

    protected function casts(): array
    {
        return [
            'test_score' => 'decimal:2',
            'row_number' => 'integer',
            'is_valid' => 'boolean',
        ];
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function mergedResults(): HasMany
    {
        return $this->hasMany(MergedResult::class);
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