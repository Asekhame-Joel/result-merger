<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'test_max',
        'exam_max',
        'total_max',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'test_max' => 'decimal:2',
            'exam_max' => 'decimal:2',
            'total_max' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (GradingSetting $setting): void {
            if (!$setting->is_active) {
                return;
            }

            static::query()
                ->whereKeyNot($setting->getKey())
                ->where('is_active', true)
                ->update(['is_active' => false]);
        });
    }

    public static function active(): ?self
    {
        return self::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    public function activate(): void
    {
        static::query()
            ->whereKeyNot($this->getKey())
            ->update(['is_active' => false]);

        $this->update(['is_active' => true]);
    }
}