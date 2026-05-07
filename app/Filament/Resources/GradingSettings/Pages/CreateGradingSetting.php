<?php

namespace App\Filament\Resources\GradingSettings\Pages;

use App\Filament\Resources\GradingSettings\GradingSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGradingSetting extends CreateRecord
{
    protected static string $resource = GradingSettingResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Grading setting created';
    }
}