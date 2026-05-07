<?php

namespace App\Models;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
        'file_name',
        'original_file_name',
        'file_path',
        'disk',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'issue_count',
        'error_message',
        'started_at',
        'completed_at',
        'failed_at',
        'created_by',
        'file_hash',
    ];

    protected function casts(): array
    {
        return [
            'type' => ImportBatchType::class,
            'status' => ImportBatchStatus::class,
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'successful_rows' => 'integer',
            'failed_rows' => 'integer',
            'issue_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function testScores(): HasMany
    {
        return $this->hasMany(TestScore::class);
    }

    public function examScores(): HasMany
    {
        return $this->hasMany(ExamScore::class);
    }

    public function mergedResults(): HasMany
    {
        return $this->hasMany(MergedResult::class, 'merge_batch_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ResultIssue::class);
    }

    public function markProcessing(): void
    {
        $this->update([
            'status' => ImportBatchStatus::Processing,
            'started_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => $this->issue_count > 0
                ? ImportBatchStatus::CompletedWithIssues
                : ImportBatchStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => ImportBatchStatus::Failed,
            'failed_at' => now(),
            'error_message' => $message,
        ]);
    }

    public function progressPercentage(): int
    {
        if ($this->total_rows <= 0) {
            return 0;
        }

        return min(100, (int) round(($this->processed_rows / $this->total_rows) * 100));
    }
}