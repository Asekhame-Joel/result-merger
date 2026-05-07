<?php

namespace App\Filament\Resources\GradeGuides\Pages;

use App\Filament\Resources\GradeGuides\GradeGuideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGradeGuide extends EditRecord
{
    protected static string $resource = GradeGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Grade band updated';
    }
}