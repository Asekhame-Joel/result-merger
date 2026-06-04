<?php


namespace App\Filament\Resources\ResultIssues;
use App\Services\Results\IssueSourceRecordUpdateService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Enums\ResultIssueSeverity;
use App\Enums\ResultIssueStatus;
use App\Enums\ResultIssueType;
use App\Filament\Resources\ExamScores\ExamScoreResource;
use App\Filament\Resources\ResultIssues\Pages\ListResultIssues;
use App\Filament\Resources\ResultIssues\Pages\ViewResultIssue;
use App\Filament\Resources\TestScores\TestScoreResource;
use App\Models\ResultIssue;
use App\Enums\ImportBatchType;
use App\Models\ExamScore;
use App\Models\ImportBatch;
use App\Models\TestScore;
use App\Services\Results\ManualScoreValidationService;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ResultIssueResource extends Resource
{
    protected static ?string $model = ResultIssue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static UnitEnum|string|null $navigationGroup = 'Results';

    protected static ?string $navigationLabel = 'Issues';

    protected static ?string $modelLabel = 'Issue';

    protected static ?string $pluralModelLabel = 'Issues';

    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Issue Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(
                                fn(ResultIssueType|string|null $state): string => $state instanceof ResultIssueType
                                ? $state->label()
                                : ucfirst((string) $state)
                            ),

                        TextEntry::make('severity')
                            ->label('Severity')
                            ->badge()
                            ->color(
                                fn(ResultIssueSeverity|string|null $state): string => $state instanceof ResultIssueSeverity
                                ? $state->color()
                                : 'gray'
                            )
                            ->formatStateUsing(
                                fn(ResultIssueSeverity|string|null $state): string => $state instanceof ResultIssueSeverity
                                ? $state->label()
                                : ucfirst((string) $state)
                            ),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(
                                fn(ResultIssueStatus|string|null $state): string => $state instanceof ResultIssueStatus
                                ? $state->color()
                                : 'gray'
                            )
                            ->formatStateUsing(
                                fn(ResultIssueStatus|string|null $state): string => $state instanceof ResultIssueStatus
                                ? $state->label()
                                : ucfirst((string) $state)
                            ),

                        TextEntry::make('message')
                            ->label('Message')
                            ->columnSpanFull(),
                    ]),

                Section::make('Student Information')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('student_id')
                            ->label('Student ID')
                            ->placeholder('Missing'),

                        TextEntry::make('matric_no')
                            ->label('Matric No')
                            ->placeholder('Missing'),

                        TextEntry::make('level')
                            ->label('Level')
                            ->placeholder('Missing'),

                        TextEntry::make('department')
                            ->label('Department')
                            ->placeholder('Missing'),
                    ]),

                Section::make('Source')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('importBatch.name')
                            ->label('Import Batch')
                            ->placeholder('No batch'),

                        TextEntry::make('row_number')
                            ->label('CSV Row')
                            ->placeholder('N/A'),

                        TextEntry::make('testScore.test_score')
                            ->label('Test Score')
                            ->placeholder('N/A'),

                        TextEntry::make('examScore.exam_score')
                            ->label('Exam Score')
                            ->placeholder('N/A'),
                    ]),

                Section::make('Resolution')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('resolver.name')
                            ->label('Resolved By')
                            ->placeholder('Not resolved'),

                        TextEntry::make('resolved_at')
                            ->label('Resolved At')
                            ->dateTime()
                            ->placeholder('Not resolved'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->groups([
                Group::make('type')
                    ->label('Issue Type')
                    ->collapsible(),

                Group::make('importBatch.name')
                    ->label('Batch')
                    ->collapsible(),

                Group::make('department')
                    ->label('Department')
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(
                        fn(ResultIssueType|string|null $state): string => $state instanceof ResultIssueType
                        ? $state->label()
                        : ucfirst((string) $state)
                    ),

                TextColumn::make('severity')
                    ->label('Severity')
                    ->badge()
                    ->color(
                        fn(ResultIssueSeverity|string|null $state): string => $state instanceof ResultIssueSeverity
                        ? $state->color()
                        : 'gray'
                    )
                    ->sortable()
                    ->formatStateUsing(
                        fn(ResultIssueSeverity|string|null $state): string => $state instanceof ResultIssueSeverity
                        ? $state->label()
                        : ucfirst((string) $state)
                    ),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn(ResultIssueStatus|string|null $state): string => $state instanceof ResultIssueStatus
                        ? $state->color()
                        : 'gray'
                    )
                    ->sortable()
                    ->formatStateUsing(
                        fn(ResultIssueStatus|string|null $state): string => $state instanceof ResultIssueStatus
                        ? $state->label()
                        : ucfirst((string) $state)
                    ),

                TextColumn::make('message')
                    ->label('Message')
                    ->searchable()
                    ->limit(50),

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
                TextColumn::make('score_source')
                    ->label('Source')
                    ->state(function (ResultIssue $record): string {
                        if ($record->test_score_id) {
                            return 'Test';
                        }

                        if ($record->exam_score_id) {
                            return 'Exam';
                        }

                        return 'N/A';
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Test' => 'info',
                        'Exam' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('testScore.test_score')
                    ->label('Test Score')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('N/A')
                    ->toggleable(),

                TextColumn::make('examScore.exam_score')
                    ->label('Exam Score')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('N/A')
                    ->toggleable(),


                TextColumn::make('level')
                    ->label('Level')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('department')
                    ->label('Department')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('importBatch.name')
                    ->label('Batch')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('row_number')
                    ->label('Row')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(collect(ResultIssueType::cases())->mapWithKeys(
                        fn(ResultIssueType $type): array => [$type->value => $type->label()]
                    )->all()),

                SelectFilter::make('severity')
                    ->options(collect(ResultIssueSeverity::cases())->mapWithKeys(
                        fn(ResultIssueSeverity $severity): array => [$severity->value => $severity->label()]
                    )->all()),

                SelectFilter::make('status')
                    ->options(collect(ResultIssueStatus::cases())->mapWithKeys(
                        fn(ResultIssueStatus $status): array => [$status->value => $status->label()]
                    )->all()),

                SelectFilter::make('import_batch_id')
                    ->label('Batch')
                    ->relationship('importBatch', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('editSourceRecord')
                    ->label('Edit Source')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('warning')
                    ->visible(fn(ResultIssue $record): bool => filled($record->test_score_id) || filled($record->exam_score_id))
                    ->modalHeading(fn(ResultIssue $record): string => $record->test_score_id
                        ? 'Edit Linked Test Score Record'
                        : 'Edit Linked Exam Score Record')
                    ->modalDescription('Update the real source record directly from this issue. Changes will reflect in Test Scores or Exam Scores.')
                    ->fillForm(function (ResultIssue $record): array {
                        $source = $record->testScore ?: $record->examScore;

                        return [
                            'student_id' => $source?->student_id,
                            'matric_no' => $source?->matric_no,
                            'first_name' => $source?->first_name,
                            'last_name' => $source?->last_name,
                            'level' => $source?->level,
                            'college' => $source?->college,
                            'department' => $source?->department,
                            'score' => $record->testScore
                                ? $record->testScore->test_score
                                : $record->examScore?->exam_score,
                            'resolve_after_save' => true,
                        ];
                    })
                    ->schema([
                        TextInput::make('student_id')
                            ->label('Student ID')
                            ->maxLength(255),

                        TextInput::make('matric_no')
                            ->label('Matric No')
                            ->maxLength(255),

                        TextInput::make('first_name')
                            ->label('First Name')
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->maxLength(255),

                        TextInput::make('level')
                            ->label('Level')
                            ->maxLength(255),

                        TextInput::make('college')
                            ->label('College')
                            ->maxLength(255),

                        TextInput::make('department')
                            ->label('Department')
                            ->maxLength(255),

                        TextInput::make('score')
                            ->label(fn(ResultIssue $record): string => $record->test_score_id ? 'Test Score' : 'Exam Score')
                            ->numeric()
                            ->minValue(0),

                        Toggle::make('resolve_after_save')
                            ->label('Mark issue as resolved after saving')
                            ->default(true),
                    ])
                    ->action(function (ResultIssue $record, array $data): void {
                        app(IssueSourceRecordUpdateService::class)
                            ->updateFromIssue($record, $data, Auth::id());

                        Notification::make()
                            ->title('Source record updated')
                            ->body('The linked test or exam score record has been updated and revalidated.')
                            ->success()
                            ->send();
                    }),
                Action::make('editTestScore')
                    ->label('Edit Test')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('info')
                    ->visible(fn(ResultIssue $record): bool => filled($record->test_score_id))
                    ->url(fn(ResultIssue $record): string => TestScoreResource::getUrl('edit', [
                        'record' => $record->test_score_id,
                    ])),

                Action::make('editExamScore')
                    ->label('Edit Exam')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('info')
                    ->visible(fn(ResultIssue $record): bool => filled($record->exam_score_id))
                    ->url(fn(ResultIssue $record): string => ExamScoreResource::getUrl('edit', [
                        'record' => $record->exam_score_id,
                    ])),

                Action::make('addTestScore')
                    ->label('Add Test')
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->color('success')
                    ->visible(fn(ResultIssue $record): bool => self::issueTypeIs($record, ResultIssueType::MissingTestRecord))
                    ->url(fn(): string => TestScoreResource::getUrl('create')),

                Action::make('addExamScore')
                    ->label('Add Exam')
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->color('success')
                    ->visible(fn(ResultIssue $record): bool => self::issueTypeIs($record, ResultIssueType::MissingExamRecord))
                    ->url(fn(): string => ExamScoreResource::getUrl('create')),

                Action::make('resolve')
                    ->label('Resolve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn(ResultIssue $record): bool => $record->status !== ResultIssueStatus::Resolved)
                    ->requiresConfirmation()
                    ->modalHeading('Mark issue as resolved?')
                    ->modalDescription('Use this after you have fixed the real test or exam score record.')
                    ->action(function (ResultIssue $record): void {
                        $record->resolve(Auth::id());

                        Notification::make()
                            ->title('Issue resolved')
                            ->success()
                            ->send();
                    }),

                Action::make('ignore')
                    ->label('Ignore')
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->color('gray')
                    ->visible(fn(ResultIssue $record): bool => $record->status !== ResultIssueStatus::Ignored)
                    ->requiresConfirmation()
                    ->modalHeading('Ignore this issue?')
                    ->modalDescription('Use this only when the issue is known and should not block your workflow.')
                    ->action(function (ResultIssue $record): void {
                        $record->ignore(Auth::id());

                        Notification::make()
                            ->title('Issue ignored')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assignTestScoreToSelected')
                        ->label('Assign Test Score')
                        ->icon(Heroicon::OutlinedPlusCircle)
                        ->color('success')
                        ->schema([
                            Select::make('import_batch_id')
                                ->label('Test Batch')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(fn(): array => ImportBatch::query()
                                    ->where('type', ImportBatchType::Test)
                                    ->latest('id')
                                    ->pluck('name', 'id')
                                    ->all()),

                            TextInput::make('test_score')
                                ->label('Test Score To Assign')
                                ->numeric()
                                ->required()
                                ->minValue(0),

                            Toggle::make('resolve_after_create')
                                ->label('Mark selected issues as resolved after creating scores')
                                ->default(true),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Assign one test score to selected missing test issues?')
                        ->modalDescription('This will create test score records for selected Missing Test issues using the student details from the related exam records.')
                        ->action(function (Collection $records, array $data): void {
                            self::bulkAssignMissingTestScores($records, $data);
                        }),

                    BulkAction::make('assignExamScoreToSelected')
                        ->label('Assign Exam Score')
                        ->icon(Heroicon::OutlinedPlusCircle)
                        ->color('success')
                        ->schema([
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

                            TextInput::make('exam_score')
                                ->label('Exam Score To Assign')
                                ->numeric()
                                ->required()
                                ->minValue(0),

                            Toggle::make('resolve_after_create')
                                ->label('Mark selected issues as resolved after creating scores')
                                ->default(true),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Assign one exam score to selected missing exam issues?')
                        ->modalDescription('This will create exam score records for selected Missing Exam issues using the student details from the related test records.')
                        ->action(function (Collection $records, array $data): void {
                            self::bulkAssignMissingExamScores($records, $data);
                        }),
                    BulkAction::make('resolveSelected')
                        ->label('Resolve Selected')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Resolve selected issues?')
                        ->modalDescription('Only do this after you have fixed the related records.')
                        ->action(function (Collection $records): void {
                            $records->each(function (ResultIssue $record): void {
                                $record->resolve(Auth::id());
                            });

                            Notification::make()
                                ->title('Selected issues resolved')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('ignoreSelected')
                        ->label('Ignore Selected')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Ignore selected issues?')
                        ->modalDescription('Ignored issues remain in the system but are marked as intentionally skipped.')
                        ->action(function (Collection $records): void {
                            $records->each(function (ResultIssue $record): void {
                                $record->ignore(Auth::id());
                            });

                            Notification::make()
                                ->title('Selected issues ignored')
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    protected static function issueTypeIs(ResultIssue $record, ResultIssueType $type): bool
    {
        if ($record->type instanceof ResultIssueType) {
            return $record->type === $type;
        }

        return $record->type === $type->value;
    }
    protected static function bulkAssignMissingTestScores(Collection $records, array $data): void
    {
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($records, $data, &$created, &$skipped): void {
            foreach ($records as $issue) {
                if (!self::issueTypeIs($issue, ResultIssueType::MissingTestRecord)) {
                    $skipped++;

                    continue;
                }

                $examScore = $issue->examScore;

                if (!$examScore && $issue->exam_score_id) {
                    $examScore = ExamScore::query()->find($issue->exam_score_id);
                }

                if (!$examScore) {
                    $skipped++;

                    continue;
                }

                $alreadyExists = TestScore::query()
                    ->where('import_batch_id', $data['import_batch_id'])
                    ->where(function ($query) use ($examScore): void {
                        $query
                            ->when($examScore->student_id, fn($query) => $query->orWhere('student_id', $examScore->student_id))
                            ->when($examScore->matric_no, fn($query) => $query->orWhere('matric_no', $examScore->matric_no));
                    })
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;

                    continue;
                }

                $scoreData = [
                    'import_batch_id' => $data['import_batch_id'],
                    'student_id' => $examScore->student_id,
                    'matric_no' => $examScore->matric_no,
                    'first_name' => $examScore->first_name,
                    'last_name' => $examScore->last_name,
                    'level' => $examScore->level,
                    'college' => $examScore->college,
                    'department' => $examScore->department,
                    'test_score' => $data['test_score'],
                    'row_number' => null,
                ];

                $scoreData = app(ManualScoreValidationService::class)
                    ->prepareTestScoreData($scoreData);

                TestScore::query()->create($scoreData);

                if ((bool) ($data['resolve_after_create'] ?? true)) {
                    $issue->resolve(Auth::id());
                }

                $created++;
            }
        });

        Notification::make()
            ->title('Test scores assigned')
            ->body("Created {$created} test score record(s). Skipped {$skipped}.")
            ->success()
            ->send();
    }

    protected static function bulkAssignMissingExamScores(Collection $records, array $data): void
    {
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($records, $data, &$created, &$skipped): void {
            foreach ($records as $issue) {
                if (!self::issueTypeIs($issue, ResultIssueType::MissingExamRecord)) {
                    $skipped++;

                    continue;
                }

                $testScore = $issue->testScore;

                if (!$testScore && $issue->test_score_id) {
                    $testScore = TestScore::query()->find($issue->test_score_id);
                }

                if (!$testScore) {
                    $skipped++;

                    continue;
                }

                $alreadyExists = ExamScore::query()
                    ->where('import_batch_id', $data['import_batch_id'])
                    ->where(function ($query) use ($testScore): void {
                        $query
                            ->when($testScore->student_id, fn($query) => $query->orWhere('student_id', $testScore->student_id))
                            ->when($testScore->matric_no, fn($query) => $query->orWhere('matric_no', $testScore->matric_no));
                    })
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;

                    continue;
                }

                $scoreData = [
                    'import_batch_id' => $data['import_batch_id'],
                    'student_id' => $testScore->student_id,
                    'matric_no' => $testScore->matric_no,
                    'first_name' => $testScore->first_name,
                    'last_name' => $testScore->last_name,
                    'level' => $testScore->level,
                    'college' => $testScore->college,
                    'department' => $testScore->department,
                    'exam_score' => $data['exam_score'],
                    'row_number' => null,
                ];

                $scoreData = app(ManualScoreValidationService::class)
                    ->prepareExamScoreData($scoreData);

                ExamScore::query()->create($scoreData);

                if ((bool) ($data['resolve_after_create'] ?? true)) {
                    $issue->resolve(Auth::id());
                }

                $created++;
            }
        });

        Notification::make()
            ->title('Exam scores assigned')
            ->body("Created {$created} exam score record(s). Skipped {$skipped}.")
            ->success()
            ->send();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'importBatch',
                'testScore',
                'examScore',
                'resolver',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResultIssues::route('/'),
            'view' => ViewResultIssue::route('/{record}'),
        ];
    }
}