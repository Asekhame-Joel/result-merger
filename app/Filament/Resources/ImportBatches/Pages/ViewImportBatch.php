<?php

namespace App\Filament\Resources\ImportBatches\Pages;

use App\Enums\ImportBatchStatus;
use App\Filament\Resources\ImportBatches\ImportBatchResource;
use App\Services\Cleanup\ResultCleanupService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewImportBatch extends ViewRecord
{
    protected static string $resource = ImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deleteBatch')
                ->label('Delete Batch')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete import batch?')
                ->modalDescription('This will delete this batch and related records. This action cannot be undone.')
                ->visible(fn(): bool => !in_array($this->record->status, [
                    ImportBatchStatus::Processing,
                    ImportBatchStatus::Pending,
                ], true))
                ->action(function (): void {
                    app(ResultCleanupService::class)->deleteBatch($this->record);

                    Notification::make()
                        ->title('Batch deleted')
                        ->success()
                        ->send();

                    $this->redirect(ImportBatchResource::getUrl('index'));
                }),
        ];
    }
}