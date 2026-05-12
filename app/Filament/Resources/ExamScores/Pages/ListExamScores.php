<?php

namespace App\Filament\Resources\ExamScores\Pages;

use App\Filament\Resources\ExamScores\ExamScoreResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExamScores extends ListRecords
{
    protected static string $resource = ExamScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Exam Score'),
        ];
    }
}