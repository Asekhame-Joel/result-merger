<?php

namespace App\Filament\Resources\ResultIssues\Pages;

use App\Filament\Resources\ResultIssues\ResultIssueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewResultIssue extends ViewRecord
{
    protected static string $resource = ResultIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }
}