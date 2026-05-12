<?php

namespace App\Filament\Resources\MergedResults;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MergedResults\Pages\CreateMergedResult;
use App\Filament\Resources\MergedResults\Pages\EditMergedResult;
use App\Filament\Resources\MergedResults\Pages\ListMergedResults;
use App\Filament\Resources\MergedResults\Schemas\MergedResultForm;
use App\Filament\Resources\MergedResults\Tables\MergedResultsTable;
use App\Models\MergedResult;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use App\Filament\Resources\MergedResults\Pages\ViewMergedResult;
use BackedEnum;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MergedResultResource extends Resource
{
    protected static ?string $model = MergedResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('student_id')
                            ->label('Student ID')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('matric_no')
                            ->label('Matric No')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('first_name')
                            ->label('First Name')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('level')
                            ->label('Level')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('department')
                            ->label('Department')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('Editable Scores')
                    ->description('Edit the final merged scores. Total score and grade will be recalculated automatically after saving.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('test_score')
                            ->label('Test Score')
                            ->numeric()
                            ->minValue(0),

                        TextInput::make('exam_score')
                            ->label('Exam Score')
                            ->numeric()
                            ->minValue(0),
                    ]),

                Section::make('Calculated Result')
                    ->columns(3)
                    ->schema([
                        TextInput::make('total_score')
                            ->label('Total Score')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('grade')
                            ->label('Grade')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('remark')
                            ->label('Remark')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return MergedResultsTable::configure($table)
            ->defaultSort('is_valid', 'asc')
            ->recordActions([
                ViewAction::make(),

                EditAction::make(),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete merged result?')
                    ->modalDescription('This removes the record from final merged results and export output. It does not delete the original test or exam score records.'),
            ])
            ->columns([
                TextColumn::make('test_score')
                    ->label('Test')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('Missing'),

                TextColumn::make('exam_score')
                    ->label('Exam')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('Missing'),

                TextColumn::make('total_score')
                    ->label('Total')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('N/A'),

                TextColumn::make('grade')
                    ->label('Grade')
                    ->badge()
                    ->sortable()
                    ->placeholder('N/A'),

                TextColumn::make('is_valid')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Valid' : 'Invalid')
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger'),

                TextColumn::make('validation_message')
                    ->label('Issue')
                    ->searchable()
                    ->limit(45)
                    ->placeholder('No issue')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('student_id')
                    ->label('Student')
                    ->searchable()
                    ->options(fn(): array => MergedResult::query()
                        ->whereNotNull('student_id')
                        ->where('student_id', '!=', '')
                        ->distinct()
                        ->orderBy('student_id')
                        ->pluck('student_id', 'student_id')
                        ->mapWithKeys(fn($value, $key): array => [
                            (string) $key => (string) $value,
                        ])
                        ->all()),

                SelectFilter::make('matric_no')
                    ->label('Matric No')
                    ->searchable()
                    ->options(fn(): array => MergedResult::query()
                        ->whereNotNull('matric_no')
                        ->where('matric_no', '!=', '')
                        ->distinct()
                        ->orderBy('matric_no')
                        ->pluck('matric_no', 'matric_no')
                        ->mapWithKeys(fn($value, $key): array => [
                            (string) $key => (string) $value,
                        ])
                        ->all()),

                SelectFilter::make('level')
                    ->label('Level')
                    ->searchable()
                    ->options(fn(): array => MergedResult::query()
                        ->whereNotNull('level')
                        ->where('level', '!=', '')
                        ->distinct()
                        ->orderBy('level')
                        ->pluck('level', 'level')
                        ->mapWithKeys(fn($value, $key): array => [
                            (string) $key => (string) $value,
                        ])
                        ->all()),

                SelectFilter::make('department')
                    ->label('Department')
                    ->searchable()
                    ->options(fn(): array => MergedResult::query()
                        ->whereNotNull('department')
                        ->where('department', '!=', '')
                        ->distinct()
                        ->orderBy('department')
                        ->pluck('department', 'department')
                        ->mapWithKeys(fn($value, $key): array => [
                            (string) $key => (string) $value,
                        ])
                        ->all()),

                TernaryFilter::make('is_valid')
                    ->label('Validation Status')
                    ->trueLabel('Valid')
                    ->falseLabel('Invalid')
                    ->native(false),
                SelectFilter::make('score_issue')
                    ->label('Score Issue')
                    ->options([
                        'missing_test' => 'Missing Test Score',
                        'missing_exam' => 'Missing Exam Score',
                        'missing_both' => 'Missing Test & Exam',
                        'invalid_total' => 'Invalid Total Score',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'missing_test' => $query->whereNull('test_score')->whereNotNull('exam_score'),
                            'missing_exam' => $query->whereNotNull('test_score')->whereNull('exam_score'),
                            'missing_both' => $query->whereNull('test_score')->whereNull('exam_score'),
                            'invalid_total' => $query
                                ->where('is_valid', false)
                                ->whereNotNull('test_score')
                                ->whereNotNull('exam_score'),
                            default => $query,
                        };
                    }),


            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
