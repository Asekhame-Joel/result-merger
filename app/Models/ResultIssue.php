<?php

namespace App\Models;

use App\Enums\ResultIssueSeverity;
use App\Enums\ResultIssueStatus;
use App\Enums\ResultIssueType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_batch_id',
        'merged_result_id',
        'test_score_id',
        'exam_score_id',
        'type',
        'severity',
        'status',
        'message',
        'row_number',
        'student_id',
        'matric_no',
        'level',
        'department',
        'metadata',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => ResultIssueType::class,
            'severity' => ResultIssueSeverity::class,
            'status' => ResultIssueStatus::class,
            'metadata' => 'array',
            'row_number' => 'integer',
            'resolved_at' => 'datetime',
        ];
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function mergedResult(): BelongsTo
    {
        return $this->belongsTo(MergedResult::class);
    }

    public function testScore(): BelongsTo
    {
        return $this->belongsTo(TestScore::class);
    }

    public function examScore(): BelongsTo
    {
        return $this->belongsTo(ExamScore::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function resolve(?int $userId = null): void
    {
        $this->update([
            'status' => ResultIssueStatus::Resolved,
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);
    }

    public function ignore(?int $userId = null): void
    {
        $this->update([
            'status' => ResultIssueStatus::Ignored,
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);
    }
    public function setLevelAttribute(?string $value): void
    {
        $this->attributes['level'] = $value ? strtoupper(trim($value)) : null;
    }

    public function setDepartmentAttribute(?string $value): void
    {
        $this->attributes['department'] = $value ? trim($value) : null;
    }


    public function setStudentIdAttribute(?string $value): void
    {
        $this->attributes['student_id'] = $value ? trim($value) : null;
    }

    public function setMatricNoAttribute(?string $value): void
    {
        $this->attributes['matric_no'] = $value ? strtoupper(trim($value)) : null;
    }
}