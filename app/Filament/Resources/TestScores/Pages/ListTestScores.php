<?php

namespace App\Filament\Resources\TestScores\Pages;

use App\Filament\Resources\TestScores\TestScoreResource;
use Filament\Resources\Pages\ListRecords;

class ListTestScores extends ListRecords
{
    protected static string $resource = TestScoreResource::class;
}