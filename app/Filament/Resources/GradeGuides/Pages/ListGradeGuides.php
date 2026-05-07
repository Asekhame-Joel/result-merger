<?php

namespace App\Filament\Resources\GradeGuides\Pages;

use App\Filament\Resources\GradeGuides\GradeGuideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGradeGuides extends ListRecords
{
    protected static string $resource = GradeGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Grade Band'),
        ];
    }
}