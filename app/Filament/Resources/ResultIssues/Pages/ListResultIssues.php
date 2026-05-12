<?php

namespace App\Filament\Resources\ResultIssues\Pages;

use App\Enums\ResultIssueStatus;
use App\Enums\ResultIssueType;
use App\Filament\Resources\ResultIssues\ResultIssueResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Tabs\Tab;
class ListResultIssues extends ListRecords
{
    protected static string $resource = ResultIssueResource::class;

    public function getTabs(): array
    {
        return [
            'open' => Tab::make('Open')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('status', ResultIssueStatus::Open)),

            'missing_tests' => Tab::make('Missing Tests')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('status', ResultIssueStatus::Open)
                    ->where('type', ResultIssueType::MissingTestRecord)),

            'missing_exams' => Tab::make('Missing Exams')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('status', ResultIssueStatus::Open)
                    ->where('type', ResultIssueType::MissingExamRecord)),

            'missing_ids' => Tab::make('Missing IDs')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('status', ResultIssueStatus::Open)
                    ->where('type', ResultIssueType::MissingStudentId)),

            'duplicates' => Tab::make('Duplicates')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('status', ResultIssueStatus::Open)
                    ->whereIn('type', [
                        ResultIssueType::DuplicateStudentId,
                        ResultIssueType::DuplicateMatricNo,
                    ])),

            'invalid_scores' => Tab::make('Invalid Scores')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('status', ResultIssueStatus::Open)
                    ->whereIn('type', [
                        ResultIssueType::InvalidTestScore,
                        ResultIssueType::InvalidExamScore,
                        ResultIssueType::InvalidTotalScore,
                    ])),

            'resolved' => Tab::make('Resolved')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('status', ResultIssueStatus::Resolved)),

            'ignored' => Tab::make('Ignored')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query
                    ->where('status', ResultIssueStatus::Ignored)),

            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'open';
    }
}