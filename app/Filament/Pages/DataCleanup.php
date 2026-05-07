<?php

namespace App\Filament\Pages;

use App\Services\Cleanup\ResultCleanupService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Throwable;
use UnitEnum;

class DataCleanup extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrash;

    protected static UnitEnum|string|null $navigationGroup = 'Processing';

    protected static ?string $navigationLabel = 'Data Cleanup';

    protected static ?string $title = 'Data Cleanup';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.data-cleanup';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deleteAllIssues')
                ->label('Delete All Issues')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Delete all issue records?')
                ->modalDescription('This deletes all issue records only. Uploaded scores and merged results will remain.')
                ->action(function (): void {
                    $this->runCleanup(
                        callback: fn() => app(ResultCleanupService::class)->deleteAllIssues(),
                        successMessage: 'All issues deleted.'
                    );
                }),

            Action::make('deleteAllMergedResults')
                ->label('Delete All Merged Results')
                ->icon(Heroicon::OutlinedDocumentMinus)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete all merged results?')
                ->modalDescription('This deletes all merged results and merge batches. Test and exam uploads will remain.')
                ->action(function (): void {
                    $this->runCleanup(
                        callback: fn() => app(ResultCleanupService::class)->deleteAllMergedResults(),
                        successMessage: 'All merged results deleted.'
                    );
                }),

            Action::make('resetProcessingData')
                ->label('Reset All Processing Data')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset all processing data?')
                ->modalDescription('This deletes all import batches, test scores, exam scores, merged results, and issues. This cannot be undone.')
                ->modalSubmitActionLabel('Yes, reset everything')
                ->action(function (): void {
                    $this->runCleanup(
                        callback: fn() => app(ResultCleanupService::class)->resetProcessingData(),
                        successMessage: 'All processing data reset.'
                    );
                }),
        ];
    }

    public function deleteTestUploads(): void
    {
        $this->runCleanup(
            callback: fn() => app(ResultCleanupService::class)->deleteAllTestUploads(),
            successMessage: 'All test uploads deleted.'
        );
    }

    public function deleteExamUploads(): void
    {
        $this->runCleanup(
            callback: fn() => app(ResultCleanupService::class)->deleteAllExamUploads(),
            successMessage: 'All exam uploads deleted.'
        );
    }

    protected function runCleanup(callable $callback, string $successMessage): void
    {
        try {
            $callback();

            Notification::make()
                ->title($successMessage)
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Cleanup failed')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
}