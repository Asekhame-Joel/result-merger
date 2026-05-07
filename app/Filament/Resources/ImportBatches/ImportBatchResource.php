<?php

namespace App\Filament\Resources\ImportBatches;
use App\Services\Cleanup\ResultCleanupService;
use Filament\Notifications\Notification;
use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Filament\Resources\ImportBatches\Pages\ListImportBatches;
use App\Filament\Resources\ImportBatches\Pages\ViewImportBatch;
use App\Models\ImportBatch;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ProgressColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ImportBatchResource extends Resource
{
    protected static ?string $model = ImportBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Processing';

    protected static ?string $navigationLabel = 'Import Batches';

    protected static ?string $modelLabel = 'Import Batch';

    protected static ?string $pluralModelLabel = 'Import Batches';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batch Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->disabled(),

                        TextInput::make('type')
                            ->disabled(),

                        TextInput::make('status')
                            ->disabled(),

                        TextInput::make('original_file_name')
                            ->label('Original File')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batch Overview')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Batch Name')
                            ->placeholder('No batch name'),

                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn(ImportBatchType|string|null $state): string => $state instanceof ImportBatchType ? $state->label() : ucfirst((string) $state)),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn(ImportBatchStatus|string|null $state): string => $state instanceof ImportBatchStatus ? $state->color() : 'gray')
                            ->formatStateUsing(fn(ImportBatchStatus|string|null $state): string => $state instanceof ImportBatchStatus ? $state->label() : ucfirst((string) $state)),

                        TextEntry::make('progress')
                            ->label('Progress')
                            ->state(fn(ImportBatch $record): string => $record->progressPercentage() . '%'),

                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->placeholder('System'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ]),

                Section::make('File Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('original_file_name')
                            ->label('Original File Name')
                            ->placeholder('No file'),

                        TextEntry::make('file_name')
                            ->label('Stored File Name')
                            ->placeholder('No file'),

                        TextEntry::make('file_path')
                            ->label('File Path')
                            ->placeholder('No path')
                            ->columnSpanFull(),

                        TextEntry::make('disk')
                            ->label('Storage Disk')
                            ->placeholder('local'),

                        TextEntry::make('file_hash')
                            ->label('File Hash')
                            ->placeholder('No hash')
                            ->copyable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Processing Counts')
                    ->columns(5)
                    ->schema([
                        TextEntry::make('total_rows')
                            ->numeric(),

                        TextEntry::make('processed_rows')
                            ->numeric(),

                        TextEntry::make('successful_rows')
                            ->numeric(),

                        TextEntry::make('failed_rows')
                            ->numeric(),

                        TextEntry::make('issue_count')
                            ->numeric()
                            ->badge()
                            ->color(fn(int $state): string => $state > 0 ? 'danger' : 'success'),
                    ]),

                Section::make('Timeline')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('started_at')
                            ->dateTime()
                            ->placeholder('Not started'),

                        TextEntry::make('completed_at')
                            ->dateTime()
                            ->placeholder('Not completed'),

                        TextEntry::make('failed_at')
                            ->dateTime()
                            ->placeholder('Not failed'),
                    ]),

                Section::make('Error Details')
                    ->visible(fn(ImportBatch $record): bool => filled($record->error_message))
                    ->schema([
                        TextEntry::make('error_message')
                            ->label('Error Message')
                            ->columnSpanFull(),
                    ]),

                Section::make('Related Records')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('test_scores_count')
                            ->label('Test Scores')
                            ->state(fn(ImportBatch $record): int => $record->testScores()->count())
                            ->numeric(),

                        TextEntry::make('exam_scores_count')
                            ->label('Exam Scores')
                            ->state(fn(ImportBatch $record): int => $record->examScores()->count())
                            ->numeric(),

                        TextEntry::make('merged_results_count')
                            ->label('Merged Results')
                            ->state(fn(ImportBatch $record): int => $record->mergedResults()->count())
                            ->numeric(),

                        TextEntry::make('issues_count')
                            ->label('Issues')
                            ->state(fn(ImportBatch $record): int => $record->issues()->count())
                            ->numeric(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Batch')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unnamed batch')
                    ->weight('bold'),

                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn(ImportBatchType|string|null $state): string => $state instanceof ImportBatchType ? $state->label() : ucfirst((string) $state)),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn(ImportBatchStatus|string|null $state): string => $state instanceof ImportBatchStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn(ImportBatchStatus|string|null $state): string => $state instanceof ImportBatchStatus ? $state->label() : ucfirst((string) $state)),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(fn(ImportBatch $record): string => $record->progressPercentage() . '%')
                    ->badge()
                    ->color(fn(ImportBatch $record): string => match (true) {
                        $record->progressPercentage() >= 100 => 'success',
                        $record->progressPercentage() > 0 => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('processed_rows')
                    ->label('Processed')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_rows')
                    ->label('Total')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('successful_rows')
                    ->label('Success')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('failed_rows')
                    ->label('Failed')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('issue_count')
                    ->label('Issues')
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('original_file_name')
                    ->label('File')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('No file')
                    ->toggleable(),
                TextColumn::make('file_hash')
                    ->label('Hash')
                    ->limit(12)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('System')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(collect(ImportBatchType::cases())->mapWithKeys(
                        fn(ImportBatchType $type): array => [$type->value => $type->label()]
                    )->all()),

                SelectFilter::make('status')
                    ->options(collect(ImportBatchStatus::cases())->mapWithKeys(
                        fn(ImportBatchStatus $status): array => [$status->value => $status->label()]
                    )->all()),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('viewIssues')
                    ->label('Issues')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->color('danger')
                    ->visible(fn(ImportBatch $record): bool => $record->issue_count > 0)
                    ->url(fn(ImportBatch $record): string => route('filament.admin.resources.result-issues.index', [
                        'tableFilters[import_batch_id][value]' => $record->id,
                    ]))
                    ->openUrlInNewTab(),

                Action::make('deleteBatch')
                    ->label('Delete Batch')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete import batch?')
                    ->modalDescription('This will delete this batch and related records. This action cannot be undone.')
                    ->visible(fn(ImportBatch $record): bool => !in_array($record->status, [
                        ImportBatchStatus::Processing,
                        ImportBatchStatus::Pending,
                    ], true))
                    ->action(function (ImportBatch $record): void {
                        app(ResultCleanupService::class)->deleteBatch($record);

                        Notification::make()
                            ->title('Batch deleted')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('creator');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListImportBatches::route('/'),
            'view' => ViewImportBatch::route('/{record}'),
        ];
    }
}