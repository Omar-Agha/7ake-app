<?php

namespace App\Filament\Resources\Catalog\Colors;

use App\Filament\Navigations\NavigationItems;
use App\Filament\Resources\Catalog\Colors\Pages\ManageColors;
use App\Models\Catalog\Color;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ColorResource extends Resource
{
    protected static ?string $model = Color::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?string $recordTitleAttribute = 'name';
    protected static string | UnitEnum | null $navigationGroup = NavigationItems::CATALOG_MENU_GROUP;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([...static::formFields()]);
    }

    public static function formFields(): array
    {
        return [
            TextInput::make('name')
                ->required(),
            TextInput::make('code')
                ->required(),
            ColorPicker::make('hex_code')->required()
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                ColorColumn::make('hex_code')


            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Color $record) {
                        // Check if products exist before letting the modal proceed
                        if ($record->productVariants()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Deletion Blocked')
                                ->body('This category cannot be deleted because it has active products.')
                                ->send();

                            // Halts the deletion process
                            $action->halt();
                        }
                    }),
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
            'index' => ManageColors::route('/'),
        ];
    }
}
