<?php

namespace App\Filament\Resources\TestScores\Pages;

use App\Filament\Resources\TestScores\TestScoreResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTestScore extends ViewRecord
{
    protected static string $resource = TestScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }
}