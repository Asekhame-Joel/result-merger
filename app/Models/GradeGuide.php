<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'minimum_score',
        'maximum_score',
        'grade',
        'remark',
        'grade_point',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'minimum_score' => 'decimal:2',
            'maximum_score' => 'decimal:2',
            'grade_point' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('minimum_score');
    }

    public static function findForScore(float|int $score): ?self
    {
        return self::query()
            ->active()
            ->where('minimum_score', '<=', $score)
            ->where('maximum_score', '>=', $score)
            ->ordered()
            ->first();
    }

    public function setGradeAttribute(?string $value): void
    {
        $this->attributes['grade'] = $value ? strtoupper(trim($value)) : null;
    }
}