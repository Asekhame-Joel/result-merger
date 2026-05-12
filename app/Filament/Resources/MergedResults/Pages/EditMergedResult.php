<?php

namespace App\Filament\Resources\MergedResults\Pages;

use App\Filament\Resources\MergedResults\MergedResultResource;
use App\Services\Results\MergedResultManualUpdateService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMergedResult extends EditRecord
{
    protected static string $resource = MergedResultResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /*
         * We return the original data here because the actual update is handled
         * in afterSave() by the service, where total/grade are recalculated.
         */
        return $data;
    }

    protected function afterSave(): void
    {
        app(MergedResultManualUpdateService::class)
            ->updateScores($this->record, [
                'test_score' => $this->data['test_score'] ?? null,
                'exam_score' => $this->data['exam_score'] ?? null,
            ]);

        Notification::make()
            ->title('Merged result updated')
            ->body('Scores, total, and grade have been recalculated.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete merged result?')
                ->modalDescription('This removes the record from final exports, but does not delete original test or exam score records.'),
        ];
    }
}