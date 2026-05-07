<?php

namespace App\Filament\Pages;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Jobs\MergeResultBatchJob;
use App\Models\ImportBatch;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use UnitEnum;

class MergeResults extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static UnitEnum|string|null $navigationGroup = 'Processing';

    protected static ?string $navigationLabel = 'Merge Results';

    protected static ?string $title = 'Merge Results';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.merge-results';

    #[Computed]
    public function latestMergeBatch(): ?ImportBatch
    {
        return ImportBatch::query()
            ->where('type', ImportBatchType::Merge)
            ->latest('id')
            ->first();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('startMerge')
                ->label('Start Merge')
                ->icon(Heroicon::OutlinedArrowsRightLeft)
                ->color('primary')
                ->schema([
                    Select::make('test_batch_id')
                        ->label('Test Score Batch')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(fn(): array => ImportBatch::query()
                            ->where('type', ImportBatchType::Test)
                            ->whereIn('status', [
                                ImportBatchStatus::Completed,
                                ImportBatchStatus::CompletedWithIssues,
                            ])
                            ->latest('id')
                            ->pluck('name', 'id')
                            ->all()),

                    Select::make('exam_batch_id')
                        ->label('Exam Score Batch')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(fn(): array => ImportBatch::query()
                            ->where('type', ImportBatchType::Exam)
                            ->whereIn('status', [
                                ImportBatchStatus::Completed,
                                ImportBatchStatus::CompletedWithIssues,
                            ])
                            ->latest('id')
                            ->pluck('name', 'id')
                            ->all()),

                    Select::make('match_by')
                        ->label('Match Students By')
                        ->required()
                        ->options([
                            'student_id' => 'Student ID',
                            'matric_no' => 'Matric Number',
                        ])
                        ->default('student_id'),
                ])
                ->action(function (array $data): void {
                    $testBatch = ImportBatch::findOrFail($data['test_batch_id']);
                    $examBatch = ImportBatch::findOrFail($data['exam_batch_id']);

                    $mergeBatch = ImportBatch::create([
                        'name' => 'Merge - ' . $testBatch->name . ' + ' . $examBatch->name,
                        'type' => ImportBatchType::Merge,
                        'status' => ImportBatchStatus::Pending,
                        'created_by' => Auth::id(),
                    ]);

                    MergeResultBatchJob::dispatch(
                        mergeBatchId: $mergeBatch->id,
                        testBatchId: $testBatch->id,
                        examBatchId: $examBatch->id,
                        matchBy: $data['match_by'],
                    );

                    Notification::make()
                        ->title('Merge queued')
                        ->body('The selected test and exam batches will be merged by the queue worker.')
                        ->success()
                        ->send();
                }),
        ];
    }
}