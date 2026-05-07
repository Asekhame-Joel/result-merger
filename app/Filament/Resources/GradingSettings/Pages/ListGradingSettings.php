<?php

namespace App\Filament\Resources\GradingSettings\Pages;

use App\Filament\Resources\GradingSettings\GradingSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGradingSettings extends ListRecords
{
    protected static string $resource = GradingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Grading Setting'),
        ];
    }
}