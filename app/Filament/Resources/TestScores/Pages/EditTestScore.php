<?php

namespace App\Filament\Resources\TestScores\Pages;

use App\Filament\Resources\TestScores\TestScoreResource;
use App\Services\Results\BatchScoreRevalidationService;
use App\Services\Results\IssueStateService;
use App\Services\Results\ManualScoreValidationService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTestScore extends EditRecord
{
    protected static string $resource = TestScoreResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return app(ManualScoreValidationService::class)
            ->prepareTestScoreData($data, $this->record->id);
    }

    protected function afterSave(): void
    {
        app(BatchScoreRevalidationService::class)
            ->revalidateTestBatch($this->record->import_batch_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->before(function (): void {
                    session()->put('deleted_test_score_id', $this->record->id);
                    session()->put('deleted_test_score_batch_id', $this->record->import_batch_id);
                })
                ->after(function (): void {
                    $testScoreId = session()->pull('deleted_test_score_id');
                    $batchId = session()->pull('deleted_test_score_batch_id');

                    if ($testScoreId) {
                        app(IssueStateService::class)
                            ->resolveOpenIssuesForDeletedTestScore((int) $testScoreId);
                    }

                    if ($batchId) {
                        app(BatchScoreRevalidationService::class)
                            ->revalidateTestBatch((int) $batchId);
                    }
                }),
        ];
    }
}