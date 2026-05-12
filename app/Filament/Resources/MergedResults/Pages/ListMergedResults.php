<?php

namespace App\Filament\Resources\MergedResults\Pages;

use App\Filament\Resources\MergedResults\MergedResultResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;


class ListMergedResults extends ListRecords
{
    protected static string $resource = MergedResultResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'valid' => Tab::make('Valid')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('is_valid', true)),

            'invalid' => Tab::make('Invalid')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('is_valid', false)),

            'missing_test' => Tab::make('Missing Test')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->whereNull('test_score')),

            'missing_exam' => Tab::make('Missing Exam')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->whereNull('exam_score')),

            'invalid_total' => Tab::make('Invalid Total')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('is_valid', false)
                    ->whereNotNull('test_score')
                    ->whereNotNull('exam_score')),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}