<?php

namespace App\Filament\Resources\MergedResults\Pages;

use App\Filament\Resources\MergedResults\MergedResultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMergedResult extends EditRecord
{
    protected static string $resource = MergedResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
