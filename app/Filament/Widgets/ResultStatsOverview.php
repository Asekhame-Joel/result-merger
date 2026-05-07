<?php

namespace App\Filament\Widgets;

use App\Enums\ResultIssueStatus;
use App\Models\ExamScore;
use App\Models\MergedResult;
use App\Models\ResultIssue;
use App\Models\TestScore;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResultStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Result Processing Overview';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $testScores = TestScore::query()->count();
        $examScores = ExamScore::query()->count();
        $mergedResults = MergedResult::query()->count();

        $validResults = MergedResult::query()
            ->where('is_valid', true)
            ->count();

        $invalidResults = MergedResult::query()
            ->where('is_valid', false)
            ->count();

        $openIssues = ResultIssue::query()
            ->where('status', ResultIssueStatus::Open)
            ->count();

        return [
            Stat::make('Test Scores', number_format($testScores))
                ->description('Uploaded test score records')
                ->descriptionIcon(Heroicon::OutlinedClipboardDocumentList)
                ->color('info'),

            Stat::make('Exam Scores', number_format($examScores))
                ->description('Uploaded exam score records')
                ->descriptionIcon(Heroicon::OutlinedClipboardDocumentList)
                ->color('info'),

            Stat::make('Merged Results', number_format($mergedResults))
                ->description('Final merged result records')
                ->descriptionIcon(Heroicon::OutlinedDocumentCheck)
                ->color('primary'),

            Stat::make('Valid Results', number_format($validResults))
                ->description('Merged records without issues')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success'),

            Stat::make('Invalid Results', number_format($invalidResults))
                ->description('Merged records requiring attention')
                ->descriptionIcon(Heroicon::OutlinedXCircle)
                ->color($invalidResults > 0 ? 'danger' : 'success'),

            Stat::make('Open Issues', number_format($openIssues))
                ->description('Unresolved import or merge issues')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($openIssues > 0 ? 'warning' : 'success'),
        ];
    }
}