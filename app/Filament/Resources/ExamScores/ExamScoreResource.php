<?php

namespace App\Filament\Resources\ExamScores;
use App\Models\TestScore;
use App\Filament\Resources\ExamScores\Pages\ListExamScores;
use App\Filament\Resources\ExamScores\Pages\ViewExamScore;
use App\Models\ExamScore;
use BackedEnum;
use App\Services\Results\BatchScoreRevalidationService;
use App\Services\Results\IssueStateService;
use Filament\Actions\DeleteAction;
use App\Enums\ImportBatchType;
use App\Filament\Resources\ExamScores\Pages\CreateExamScore;
use App\Filament\Resources\ExamScores\Pages\EditExamScore;
use App\Models\ImportBatch;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Actions\BulkActionGroup;
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
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('import_batch_id')
                    ->label('Exam Batch')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(fn(): array => ImportBatch::query()
                        ->where('type', ImportBatchType::Exam)
                        ->latest('id')
                        ->pluck('name', 'id')
                        ->all()),

                Select::make('source_test_score_id')
                    ->label('Copy Student From Existing Test Record')
                    ->helperText('Select the student whose exam score is missing. Student details will be filled automatically.')
                    ->searchable()
                    ->preload()
                    ->dehydrated(false)
                    ->options(fn(): array => TestScore::query()
                        ->where('is_valid', true)
                        ->latest('id')
                        ->get()
                        ->mapWithKeys(fn(TestScore $testScore): array => [
                            $testScore->id => trim(
                                ($testScore->student_id ?: 'No Student ID')
                                . ' | '
                                . ($testScore->matric_no ?: 'No Matric No')
                                . ' | '
                                . ($testScore->first_name ?: '')
                                . ' '
                                . ($testScore->last_name ?: '')
                                . ' | Test: '
                                . $testScore->test_score
                            ),
                        ])
                        ->all())
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if (!$state) {
                            return;
                        }

                        $testScore = TestScore::find($state);

                        if (!$testScore) {
                            return;
                        }

                        $set('student_id', $testScore->student_id);
                        $set('matric_no', $testScore->matric_no);
                        $set('first_name', $testScore->first_name);
                        $set('last_name', $testScore->last_name);
                        $set('level', $testScore->level);
                        $set('college', $testScore->college);
                        $set('department', $testScore->department);
                    })
                    ->visible(fn(string $operation): bool => $operation === 'create'),

                TextInput::make('student_id')
                    ->label('Student ID')
                    ->maxLength(255)
                    ->disabled(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(),

                TextInput::make('matric_no')
                    ->label('Matric No')
                    ->maxLength(255)
                    ->disabled(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(),

                TextInput::make('first_name')
                    ->label('First Name')
                    ->maxLength(255)
                    ->disabled(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(),

                TextInput::make('last_name')
                    ->label('Last Name')
                    ->maxLength(255)
                    ->disabled(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(),

                TextInput::make('level')
                    ->label('Level')
                    ->maxLength(255)
                    ->disabled(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(),

                TextInput::make('college')
                    ->label('College')
                    ->maxLength(255)
                    ->disabled(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(),

                TextInput::make('department')
                    ->label('Department')
                    ->maxLength(255)
                    ->disabled(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(),

                TextInput::make('exam_score')
                    ->label('Exam Score')
                    ->numeric()
                    ->required()
                    ->minValue(0),
            ]);
    }
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

                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (ExamScore $record): void {
                        session()->put('deleted_exam_score_id', $record->id);
                        session()->put('deleted_exam_score_batch_id', $record->import_batch_id);
                    })
                    ->after(function (): void {
                        $examScoreId = session()->pull('deleted_exam_score_id');
                        $batchId = session()->pull('deleted_exam_score_batch_id');

                        if ($examScoreId) {
                            app(IssueStateService::class)
                                ->resolveOpenIssuesForDeletedExamScore((int) $examScoreId);
                        }

                        if ($batchId) {
                            app(BatchScoreRevalidationService::class)
                                ->revalidateExamBatch((int) $batchId);
                        }
                    }),
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
            'create' => CreateExamScore::route('/create'),
            'view' => ViewExamScore::route('/{record}'),
            'edit' => EditExamScore::route('/{record}/edit'),
        ];
    }
}