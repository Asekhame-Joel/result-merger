<?php

namespace App\Filament\Resources\ResultIssues\Schemas;

use App\Enums\ResultIssueSeverity;
use App\Enums\ResultIssueStatus;
use App\Enums\ResultIssueType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ResultIssueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('import_batch_id')
                    ->relationship('importBatch', 'name'),
                Select::make('merged_result_id')
                    ->relationship('mergedResult', 'id'),
                Select::make('test_score_id')
                    ->relationship('testScore', 'id'),
                Select::make('exam_score_id')
                    ->relationship('examScore', 'id'),
                Select::make('type')
                    ->options(ResultIssueType::class)
                    ->required(),
                Select::make('severity')
                    ->options(ResultIssueSeverity::class)
                    ->default('error')
                    ->required(),
                Select::make('status')
                    ->options(ResultIssueStatus::class)
                    ->default('open')
                    ->required(),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('row_number')
                    ->numeric(),
                TextInput::make('student_id'),
                TextInput::make('matric_no'),
                TextInput::make('level'),
                TextInput::make('department'),
                TextInput::make('metadata'),
                DateTimePicker::make('resolved_at'),
                TextInput::make('resolved_by')
                    ->numeric(),
            ]);
    }
}
