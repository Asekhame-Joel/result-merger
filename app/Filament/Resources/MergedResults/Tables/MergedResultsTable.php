<?php

namespace App\Filament\Resources\MergedResults\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MergedResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('test_score_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('exam_score_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('testImportBatch.name')
                    ->searchable(),
                TextColumn::make('examImportBatch.name')
                    ->searchable(),
                TextColumn::make('mergeBatch.name')
                    ->searchable(),
                TextColumn::make('student_id')
                    ->searchable(),
                TextColumn::make('matric_no')
                    ->searchable(),
                TextColumn::make('first_name')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->searchable(),
                TextColumn::make('level')
                    ->searchable(),
                TextColumn::make('college')
                    ->searchable(),
                TextColumn::make('department')
                    ->searchable(),
                TextColumn::make('test_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('exam_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('grade')
                    ->searchable(),
                TextColumn::make('remark')
                    ->searchable(),
                TextColumn::make('grade_point')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_valid')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
