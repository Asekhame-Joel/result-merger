<?php

namespace App\Filament\Resources\ExamScores;

use App\Filament\Resources\ExamScores\Pages\ListExamScores;
use App\Filament\Resources\ExamScores\Pages\ViewExamScore;
use App\Models\ExamScore;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ExamScoreResource extends Resource
{
    protected static ?string $model = ExamScore::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static UnitEnum|string|null $navigationGroup = 'Uploads';

    protected static ?string $navigationLabel = 'Exam Scores';

    protected static ?string $modelLabel = 'Exam Score';

    protected static ?string $pluralModelLabel = 'Exam Scores';

    protected static ?int $navigationSort = 4;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student Information')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('student_id')->label('Student ID')->placeholder('Missing'),
                        TextEntry::make('matric_no')->label('Matric No')->placeholder('Missing'),
                        TextEntry::make('first_name')->label('First Name')->placeholder('Missing'),
                        TextEntry::make('last_name')->label('Last Name')->placeholder('Missing'),
                    ]),

                Section::make('Academic Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('level')->label('Level')->placeholder('Missing')->badge(),
                        TextEntry::make('college')->label('College')->placeholder('Missing'),
                        TextEntry::make('department')->label('Department')->placeholder('Missing'),
                    ]),

                Section::make('Exam Score')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('exam_score')
                            ->label('Exam Score')
                            ->numeric(decimalPlaces: 2),

                        IconEntry::make('is_valid')
                            ->label('Valid')
                            ->boolean(),

                        TextEntry::make('row_number')
                            ->label('CSV Row'),
                    ]),

                Section::make('Validation')
                    ->visible(fn(ExamScore $record): bool => filled($record->validation_message))
                    ->schema([
                        TextEntry::make('validation_message')
                            ->label('Validation Message')
                            ->columnSpanFull(),
                    ]),

                Section::make('Batch')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('importBatch.name')
                            ->label('Import Batch')
                            ->placeholder('Missing'),

                        TextEntry::make('created_at')
                            ->label('Uploaded At')
                            ->dateTime(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('student_id')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Missing'),

                TextColumn::make('matric_no')
                    ->label('Matric No')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Missing'),

                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('level')
                    ->label('Level')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('college')
                    ->label('College')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('department')
                    ->label('Department')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('exam_score')
                    ->label('Exam Score')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                IconColumn::make('is_valid')
                    ->label('Valid')
                    ->boolean(),

                TextColumn::make('validation_message')
                    ->label('Issue')
                    ->limit(40)
                    ->placeholder('No issue')
                    ->toggleable(),

                TextColumn::make('importBatch.name')
                    ->label('Batch')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('row_number')
                    ->label('Row')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_valid')
                    ->label('Validity'),

                SelectFilter::make('import_batch_id')
                    ->label('Batch')
                    ->relationship('importBatch', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('department')
                    ->label('Department')
                    ->options(fn(): array => ExamScore::query()
                        ->whereNotNull('department')
                        ->distinct()
                        ->orderBy('department')
                        ->pluck('department', 'department')
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),

                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('importBatch');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamScores::route('/'),
            'view' => ViewExamScore::route('/{record}'),
        ];
    }
}