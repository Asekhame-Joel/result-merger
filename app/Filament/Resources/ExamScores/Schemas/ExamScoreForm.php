<?php

namespace App\Filament\Resources\ExamScores\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExamScoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('import_batch_id')
                    ->relationship('importBatch', 'name')
                    ->required(),
                TextInput::make('student_id'),
                TextInput::make('matric_no'),
                TextInput::make('first_name'),
                TextInput::make('last_name'),
                TextInput::make('level'),
                TextInput::make('college'),
                TextInput::make('department'),
                TextInput::make('exam_score')
                    ->numeric(),
                TextInput::make('row_number')
                    ->numeric(),
                Toggle::make('is_valid')
                    ->required(),
                Textarea::make('validation_message')
                    ->columnSpanFull(),
            ]);
    }
}
