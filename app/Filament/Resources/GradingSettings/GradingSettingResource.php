<?php

namespace App\Filament\Resources\GradingSettings;
use UnitEnum;
use App\Filament\Resources\GradingSettings\Pages\CreateGradingSetting;
use App\Filament\Resources\GradingSettings\Pages\EditGradingSetting;
use App\Filament\Resources\GradingSettings\Pages\ListGradingSettings;
use App\Models\GradingSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GradingSettingResource extends Resource
{
    protected static ?string $model = GradingSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Grading Settings';

    protected static ?string $modelLabel = 'Grading Setting';

    protected static ?string $pluralModelLabel = 'Grading Settings';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grading Configuration')
                    ->description('Define the maximum scores used for result validation and total score calculation.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Setting Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Default 40/60 Grading Setting')
                            ->columnSpanFull(),

                        TextInput::make('test_max')
                            ->label('Test Maximum Score')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(40)
                            ->helperText('Example: 40'),

                        TextInput::make('exam_max')
                            ->label('Exam Maximum Score')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(60)
                            ->helperText('Example: 60'),

                        TextInput::make('total_max')
                            ->label('Total Maximum Score')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(100)
                            ->helperText('Usually test max + exam max'),

                        Toggle::make('is_active')
                            ->label('Use as Active Setting')
                            ->helperText('Only one grading setting can be active at a time.')
                            ->default(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Setting')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('test_max')
                    ->label('Test Max')
                    ->sortable()
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('exam_max')
                    ->label('Exam Max')
                    ->sortable()
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('total_max')
                    ->label('Total Max')
                    ->sortable()
                    ->numeric(decimalPlaces: 2),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Activate')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn(GradingSetting $record): bool => !$record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('Activate grading setting')
                    ->modalDescription('This will deactivate all other grading settings.')
                    ->action(function (GradingSetting $record): void {
                        $record->activate();

                        Notification::make()
                            ->title('Grading setting activated')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),

                DeleteAction::make()
                    ->visible(fn(GradingSetting $record): bool => !$record->is_active),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $records
                                ->where('is_active', false)
                                ->each
                                ->delete();

                            Notification::make()
                                ->title('Inactive grading settings deleted')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGradingSettings::route('/'),
            'create' => CreateGradingSetting::route('/create'),
            'edit' => EditGradingSetting::route('/{record}/edit'),
        ];
    }
}