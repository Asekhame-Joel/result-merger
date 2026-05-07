<?php

namespace App\Filament\Resources\GradeGuides;
use UnitEnum;
use App\Filament\Resources\GradeGuides\Pages\CreateGradeGuide;
use App\Filament\Resources\GradeGuides\Pages\EditGradeGuide;
use App\Filament\Resources\GradeGuides\Pages\ListGradeGuides;
use App\Models\GradeGuide;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GradeGuideResource extends Resource
{
    protected static ?string $model = GradeGuide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Grade Guide';

    protected static ?string $modelLabel = 'Grade Band';

    protected static ?string $pluralModelLabel = 'Grade Guide';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grade Band')
                    ->description('Define score ranges and their corresponding grades.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('grade')
                            ->label('Grade')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('A')
                            ->helperText('Example: A, B, C, D, E, F'),

                        TextInput::make('remark')
                            ->label('Remark')
                            ->maxLength(255)
                            ->placeholder('Excellent'),

                        TextInput::make('minimum_score')
                            ->label('Minimum Score')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(1000),

                        TextInput::make('maximum_score')
                            ->label('Maximum Score')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->gte('minimum_score'),

                        TextInput::make('grade_point')
                            ->label('Grade Point')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->step('0.01')
                            ->placeholder('5.00'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('grade')
                    ->label('Grade')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('minimum_score')
                    ->label('Min')
                    ->sortable()
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('maximum_score')
                    ->label('Max')
                    ->sortable()
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('remark')
                    ->label('Remark')
                    ->searchable()
                    ->placeholder('No remark'),

                TextColumn::make('grade_point')
                    ->label('Point')
                    ->sortable()
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('-'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGradeGuides::route('/'),
            'create' => CreateGradeGuide::route('/create'),
            'edit' => EditGradeGuide::route('/{record}/edit'),
        ];
    }
}