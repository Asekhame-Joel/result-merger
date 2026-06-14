<?php

namespace App\Filament\Resources\MergedResults;

use App\Filament\Resources\MergedResults\Pages\EditMergedResult;
use App\Filament\Resources\MergedResults\Pages\ListMergedResults;
use App\Filament\Resources\MergedResults\Pages\ViewMergedResult;
use App\Models\MergedResult;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MergedResultResource extends Resource
{
    protected static ?string $model = MergedResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Merged Results';

    protected static ?string $modelLabel = 'Merged Result';

    protected static ?string $pluralModelLabel = 'Merged Results';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('student_id')
                            ->label('Student ID')
                            ->disabled(),

                        TextInput::make('matric_no')
                            ->label('Mat Number')
                            ->disabled(),

                        TextInput::make('first_name')
                            ->label('First Name')
                            ->disabled(),

                        TextInput::make('last_name')
                            ->label('Surname')
                            ->disabled(),

                        TextInput::make('level')
                            ->disabled(),

                        TextInput::make('department')
                            ->disabled(),
                    ]),

                Section::make('Scores')
                    ->columns(3)
                    ->schema([
                        TextInput::make('test_score')
                            ->label('Test Score')
                            ->numeric(),

                        TextInput::make('exam_score')
                            ->label('Exam Score')
                            ->numeric(),

                        TextInput::make('total_score')
                            ->label('Total Score')
                            ->disabled(),
                    ]),

                Section::make('Result Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('grade')
                            ->disabled(),

                        TextInput::make('remark')
                            ->disabled(),

                        TextInput::make('validation_message')
                            ->label('Issue')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('student_id')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('full_name')
                    ->label('Student Name')
                    ->formatStateUsing(
                        fn($record) => trim(
                            $record->first_name . ' ' . $record->last_name
                        )
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->wrap(),

                TextColumn::make('matric_no')
                    ->label('Mat Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('level')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('test_score')
                    ->label('Test')
                    ->numeric(decimalPlaces: 2)
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('exam_score')
                    ->label('Exam')
                    ->numeric(decimalPlaces: 2)
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('total_score')
                    ->label('Total')
                    ->numeric(decimalPlaces: 2)
                    ->badge()
                    ->sortable()
                    ->color(fn($state) => match (true) {
                        $state >= 70 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),

                BadgeColumn::make('grade')
                    ->colors([
                        'success' => ['A', 'B'],
                        'warning' => ['C'],
                        'danger' => ['D', 'E', 'F'],
                    ]),

                BadgeColumn::make('is_valid')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => $state ? 'Valid' : 'Invalid')
                    ->colors([
                        'success' => fn($state) => $state,
                        'danger' => fn($state) => !$state,
                    ]),

                TextColumn::make('validation_message')
                    ->label('Issue')
                    ->default('No issue')
                    ->limit(40)
                    ->wrap(),

                TextColumn::make('mergeBatch.name')
                    ->label('Merge Batch')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Merged At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                //
            ])

            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMergedResults::route('/'),
            'view' => ViewMergedResult::route('/{record}'),
            'edit' => EditMergedResult::route('/{record}/edit'),
        ];
    }
}