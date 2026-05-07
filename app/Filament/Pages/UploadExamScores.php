<?php

namespace App\Filament\Pages;
use App\Services\Imports\ImportUploadGuard;
use Throwable;
use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Jobs\ProcessExamScoreImportJob;
use App\Models\ImportBatch;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use UnitEnum;

class UploadExamScores extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static UnitEnum|string|null $navigationGroup = 'Uploads';

    protected static ?string $navigationLabel = 'Upload Exam Scores';

    protected static ?string $title = 'Upload Exam Scores';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.upload-exam-scores';

    #[Computed]
    public function latestExamBatch(): ?ImportBatch
    {
        return ImportBatch::query()
            ->where('type', ImportBatchType::Exam)
            ->latest('id')
            ->first();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadExamScores')
                ->label('Upload CSV File')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('primary')
                ->schema([
                    TextInput::make('name')
                        ->label('Batch Name')
                        ->required()
                        ->maxLength(255)
                        ->default('Exam Scores Upload - ' . now()->format('Y-m-d H:i')),

                    FileUpload::make('file_path')
                        ->label('CSV File')
                        ->required()
                        ->disk('local')
                        ->directory('imports/exam-scores')
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
                        $guard->preventActiveDuplicate(ImportBatchType::Exam, $fileHash);
                        $guard->preventAnyDuplicate(ImportBatchType::Exam, $fileHash);

                        $batch = ImportBatch::create([
                            'name' => $data['name'],
                            'type' => ImportBatchType::Exam,
                            'status' => ImportBatchStatus::Pending,
                            'file_path' => $filePath,
                            'file_hash' => $fileHash,
                            'disk' => $disk,
                            'file_name' => basename($filePath),
                            'original_file_name' => basename($filePath),
                            'created_by' => Auth::id(),
                        ]);

                        ProcessExamScoreImportJob::dispatch($batch->id);

                        Notification::make()
                            ->title('Exam score import queued')
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