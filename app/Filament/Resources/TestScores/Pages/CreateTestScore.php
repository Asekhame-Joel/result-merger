<?php

namespace App\Filament\Resources\TestScores\Pages;

use App\Filament\Resources\TestScores\TestScoreResource;
use App\Services\Results\ManualScoreValidationService;
use Filament\Resources\Pages\CreateRecord;

class CreateTestScore extends CreateRecord
{
    protected static string $resource = TestScoreResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['row_number'] = $data['row_number'] ?? null;

        return app(ManualScoreValidationService::class)
            ->prepareTestScoreData($data);
    }
}