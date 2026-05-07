<?php

namespace App\Filament\Resources\GradeGuides\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GradeGuideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('minimum_score')
                    ->required()
                    ->numeric(),
                TextInput::make('maximum_score')
                    ->required()
                    ->numeric(),
                TextInput::make('grade')
                    ->required(),
                TextInput::make('remark'),
                TextInput::make('grade_point')
                    ->numeric(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
