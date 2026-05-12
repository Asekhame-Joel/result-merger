<?php

namespace App\Filament\Resources\ExamScores\Pages;

use App\Filament\Resources\ExamScores\ExamScoreResource;
use App\Services\Results\ManualScoreValidationService;
use Filament\Resources\Pages\CreateRecord;

class CreateExamScore extends CreateRecord
{
    protected static string $resource = ExamScoreResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['row_number'] = $data['row_number'] ?? null;

        return app(ManualScoreValidationService::class)
            ->prepareExamScoreData($data);
    }
}