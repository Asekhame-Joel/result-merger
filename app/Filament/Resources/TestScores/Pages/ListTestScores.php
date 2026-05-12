<?php

namespace App\Filament\Resources\TestScores\Pages;

use App\Filament\Resources\TestScores\TestScoreResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTestScores extends ListRecords
{
    protected static string $resource = TestScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Test Score'),
        ];
    }
}