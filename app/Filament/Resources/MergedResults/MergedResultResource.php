<?php

namespace App\Filament\Resources\MergedResults;

use App\Filament\Resources\MergedResults\Pages\CreateMergedResult;
use App\Filament\Resources\MergedResults\Pages\EditMergedResult;
use App\Filament\Resources\MergedResults\Pages\ListMergedResults;
use App\Filament\Resources\MergedResults\Schemas\MergedResultForm;
use App\Filament\Resources\MergedResults\Tables\MergedResultsTable;
use App\Models\MergedResult;
use App\Filament\Resources\MergedResults\Pages\ViewMergedResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MergedResultResource extends Resource
{
    protected static ?string $model = MergedResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MergedResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MergedResultsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMergedResults::route('/'),
            'view' => ViewMergedResult::route('/{record}'),
        ];
    }
}
