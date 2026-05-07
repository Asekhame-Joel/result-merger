<?php

namespace App\Filament\Resources\GradingSettings\Pages;

use App\Filament\Resources\GradingSettings\GradingSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGradingSetting extends EditRecord
{
    protected static string $resource = GradingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn(): bool => !$this->record->is_active),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Grading setting updated';
    }
}