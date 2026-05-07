<?php

namespace App\Filament\Pages;
use Livewire\Attributes\Computed;
use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Jobs\ProcessTestScoreImportJob;
use App\Models\ImportBatch;
use BackedEnum;
use App\Services\Imports\ImportUploadGuard;
use Throwable;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use UnitEnum;

class UploadTestScores extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static UnitEnum|string|null $navigationGroup = 'Uploads';

    protected static ?string $navigationLabel = 'Upload Test Scores';

    protected static ?string $title = 'Upload Test Scores';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.upload-test-scores';
    #[Computed]
    public function latestTestBatch(): ?ImportBatch
    {
        return ImportBatch::query()
            ->where('type', ImportBatchType::Test)
            ->latest('id')
            ->first();
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadTestScores')
                ->label('Upload Excel File')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('primary')
                ->schema([
                    TextInput::make('name')
                        ->label('Batch Name')
                        ->required()
                        ->maxLength(255)
                        ->default('Test Scores Upload - ' . now()->format('Y-m-d H:i')),

                    FileUpload::make('file_path')
                        ->label('CSV File')
                        ->required()
                        ->disk('local')
                        ->directory('imports/test-scores')
                        ->visibility('private')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel',
                        ])
                        ->maxSize(10240)
                        ->preserveFilenames()
                        ->helperText('Upload CSV only. Save Excel as CSV UTF-8 before uploading.'),
                ])
                ->action(function (array $data): void {
                    try {
                        $filePath = $data['file_path'];
                        $disk = 'local';

                        $guard = app(ImportUploadGuard::class);

                        $fileHash = $guard->validateCsvUpload($disk, $filePath);
                        $guard->preventActiveDuplicate(ImportBatchType::Test, $fileHash);
                        $guard->preventAnyDuplicate(ImportBatchType::Test, $fileHash);

                        $batch = ImportBatch::create([
                            'name' => $data['name'],
                            'type' => ImportBatchType::Test,
                            'status' => ImportBatchStatus::Pending,
                            'file_path' => $filePath,
                            'file_hash' => $fileHash,
                            'disk' => $disk,
                            'file_name' => basename($filePath),
                            'original_file_name' => basename($filePath),
                            'created_by' => Auth::id(),
                        ]);

                        ProcessTestScoreImportJob::dispatch($batch->id);

                        Notification::make()
                            ->title('Test score import queued')
                            ->body('The CSV file has been uploaded and will be processed by the queue worker.')
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Upload rejected')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}