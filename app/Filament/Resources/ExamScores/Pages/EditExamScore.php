<?php

namespace App\Filament\Resources\ExamScores\Pages;

use App\Filament\Resources\ExamScores\ExamScoreResource;
use App\Services\Results\BatchScoreRevalidationService;
use App\Services\Results\IssueStateService;
use App\Services\Results\ManualScoreValidationService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExamScore extends EditRecord
{
    protected static string $resource = ExamScoreResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return app(ManualScoreValidationService::class)
            ->prepareExamScoreData($data, $this->record->id);
    }

    protected function afterSave(): void
    {
        app(BatchScoreRevalidationService::class)
            ->revalidateExamBatch($this->record->import_batch_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->before(function (): void {
                    session()->put('deleted_exam_score_id', $this->record->id);
                    session()->put('deleted_exam_score_batch_id', $this->record->import_batch_id);
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
        ];
    }
}