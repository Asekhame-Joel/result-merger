<?php

namespace App\Filament\Pages;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Models\ImportBatch;
use App\Services\Exports\MergedResultCsvExportService;
use App\Services\Exports\MergedResultExcelExportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
                        ->helperText('Select the merge batch to export. The Excel filename will be generated from the uploaded file name.')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(fn(): array => ImportBatch::query()
                            ->where('type', ImportBatchType::Merge)
                            ->whereIn('status', [
                                ImportBatchStatus::Completed,
                                ImportBatchStatus::CompletedWithIssues,
                            ])
                            ->latest('id')
                            ->get()
                            ->mapWithKeys(fn(ImportBatch $batch): array => [
                                $batch->id => $batch->name . ' - ' . $batch->created_at->format('M d, Y H:i'),
                            ])
                            ->all()),

                    Toggle::make('valid_only')
                        ->label('Export valid results only')
                        ->default(false)
                        ->helperText('This option is currently used by CSV export. Excel export uses the selected merge batch.'),
                ])
                ->action(function (array $data): BinaryFileResponse {
                    return app(MergedResultExcelExportService::class)
                        ->download($data['merge_batch_id'] ?? null);
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