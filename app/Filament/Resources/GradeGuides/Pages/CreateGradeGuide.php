<?php

namespace App\Filament\Resources\GradeGuides\Pages;

use App\Filament\Resources\GradeGuides\GradeGuideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGradeGuide extends CreateRecord
{
    protected static string $resource = GradeGuideResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Grade band created';
    }
}