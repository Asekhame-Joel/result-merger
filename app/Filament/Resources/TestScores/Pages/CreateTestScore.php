<?php

namespace App\Filament\Resources\TestScores\Pages;

use App\Filament\Resources\TestScores\TestScoreResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTestScore extends CreateRecord
{
    protected static string $resource = TestScoreResource::class;
}
