<?php

namespace App\Filament\Widgets;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Models\ImportBatch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentImportBatchesWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Processing Batches';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ImportBatch::query()
                    ->with('creator')
                    ->latest('id')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Batch')
                    ->limit(45)
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(ImportBatchType|string|null $state): string => $state instanceof ImportBatchType ? $state->label() : ucfirst((string) $state)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(ImportBatchStatus|string|null $state): string => $state instanceof ImportBatchStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn(ImportBatchStatus|string|null $state): string => $state instanceof ImportBatchStatus ? $state->label() : ucfirst((string) $state)),

                TextColumn::make('processed_rows')
                    ->label('Processed')
                    ->numeric(),

                TextColumn::make('total_rows')
                    ->label('Total')
                    ->numeric(),

                TextColumn::make('issue_count')
                    ->label('Issues')
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'warning' : 'success'),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('System'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}