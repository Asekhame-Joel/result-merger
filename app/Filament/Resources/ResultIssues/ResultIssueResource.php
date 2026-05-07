<?php

namespace App\Filament\Resources\ResultIssues;

use App\Enums\ResultIssueSeverity;
use App\Enums\ResultIssueStatus;
use App\Enums\ResultIssueType;
use App\Filament\Resources\ResultIssues\Pages\ListResultIssues;
use App\Filament\Resources\ResultIssues\Pages\ViewResultIssue;
use App\Models\ResultIssue;
use BackedEnum;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
                            ->formatStateUsing(fn(ResultIssueType|string|null $state): string => $state instanceof ResultIssueType ? $state->label() : ucfirst((string) $state)),

                        TextEntry::make('severity')
                            ->label('Severity')
                            ->badge()
                            ->color(fn(ResultIssueSeverity|string|null $state): string => $state instanceof ResultIssueSeverity ? $state->color() : 'gray')
                            ->formatStateUsing(fn(ResultIssueSeverity|string|null $state): string => $state instanceof ResultIssueSeverity ? $state->label() : ucfirst((string) $state)),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(ResultIssueStatus|string|null $state): string => $state instanceof ResultIssueStatus ? $state->color() : 'gray')
                            ->formatStateUsing(fn(ResultIssueStatus|string|null $state): string => $state instanceof ResultIssueStatus ? $state->label() : ucfirst((string) $state)),

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
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(ResultIssueType|string|null $state): string => $state instanceof ResultIssueType ? $state->label() : ucfirst((string) $state)),

                TextColumn::make('severity')
                    ->label('Severity')
                    ->badge()
                    ->color(fn(ResultIssueSeverity|string|null $state): string => $state instanceof ResultIssueSeverity ? $state->color() : 'gray')
                    ->sortable()
                    ->formatStateUsing(fn(ResultIssueSeverity|string|null $state): string => $state instanceof ResultIssueSeverity ? $state->label() : ucfirst((string) $state)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(ResultIssueStatus|string|null $state): string => $state instanceof ResultIssueStatus ? $state->color() : 'gray')
                    ->sortable()
                    ->formatStateUsing(fn(ResultIssueStatus|string|null $state): string => $state instanceof ResultIssueStatus ? $state->label() : ucfirst((string) $state)),

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

                Action::make('resolve')
                    ->label('Resolve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn(ResultIssue $record): bool => $record->status !== ResultIssueStatus::Resolved)
                    ->requiresConfirmation()
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
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
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