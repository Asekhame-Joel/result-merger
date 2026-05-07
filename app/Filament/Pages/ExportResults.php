<?php

namespace App\Filament\Pages;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Models\ImportBatch;
use App\Services\Exports\MergedResultCsvExportService;
use BackedEnum;
use Filament\Actions\Action;
use App\Services\Exports\MergedResultExcelExportService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ExportResults extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static UnitEnum|string|null $navigationGroup = 'Results';

    protected static ?string $navigationLabel = 'Export Results';

    protected static ?string $title = 'Export Results';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.export-results';

    public ?string $latestExportPath = null;
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportMergedResultsCsv')
                ->label('Export CSV')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->schema([
                    Select::make('merge_batch_id')
                        ->label('Merge Batch')
                        ->helperText('Leave empty to export all merged results.')
                        ->searchable()
                        ->preload()
                        ->options(fn(): array => ImportBatch::query()
                            ->where('type', ImportBatchType::Merge)
                            ->whereIn('status', [
                                ImportBatchStatus::Completed,
                                ImportBatchStatus::CompletedWithIssues,
                            ])
                            ->latest('id')
                            ->pluck('name', 'id')
                            ->all()),

                    Toggle::make('valid_only')
                        ->label('Export valid results only')
                        ->default(false),
                ])
                ->action(function (array $data): void {
                    $path = app(MergedResultCsvExportService::class)->export(
                        mergeBatchId: $data['merge_batch_id'] ?? null,
                        validOnly: (bool) ($data['valid_only'] ?? false),
                    );

                    $this->latestExportPath = $path;

                    Notification::make()
                        ->title('CSV export completed')
                        ->body('Your merged results CSV has been generated.')
                        ->success()
                        ->send();
                }),

            Action::make('exportMergedResultsExcel')
                ->label('Export Excel')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('success')
                ->schema([
                    Select::make('merge_batch_id')
                        ->label('Merge Batch')
                        ->helperText('Leave empty to export all merged results.')
                        ->searchable()
                        ->preload()
                        ->options(fn(): array => ImportBatch::query()
                            ->where('type', ImportBatchType::Merge)
                            ->whereIn('status', [
                                ImportBatchStatus::Completed,
                                ImportBatchStatus::CompletedWithIssues,
                            ])
                            ->latest('id')
                            ->pluck('name', 'id')
                            ->all()),

                    Toggle::make('valid_only')
                        ->label('Export valid results only')
                        ->default(false),
                ])
                ->action(function (array $data): void {
                    $path = app(MergedResultExcelExportService::class)->export(
                        mergeBatchId: $data['merge_batch_id'] ?? null,
                        validOnly: (bool) ($data['valid_only'] ?? false),
                    );

                    $this->latestExportPath = $path;

                    Notification::make()
                        ->title('Excel export completed')
                        ->body('Your merged results Excel file has been generated.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function downloadLatestExport()
    {
        if (!$this->latestExportPath || !Storage::disk('local')->exists($this->latestExportPath)) {
            Notification::make()
                ->title('Export file not found')
                ->danger()
                ->send();

            return null;
        }

        return Storage::disk('local')->download($this->latestExportPath);
    }
}