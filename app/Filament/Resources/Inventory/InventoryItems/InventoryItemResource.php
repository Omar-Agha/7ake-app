<?php

namespace App\Filament\Resources\Inventory\InventoryItems;

use App\Filament\Navigations\NavigationItems;
use App\Filament\Resources\Inventory\InventoryItems\Pages\CreateInventoryItem;
use App\Filament\Resources\Inventory\InventoryItems\Pages\EditInventoryItem;
use App\Filament\Resources\Inventory\InventoryItems\Pages\ListInventoryItems;
use App\Filament\Resources\Inventory\InventoryItems\Schemas\InventoryItemForm;
use App\Filament\Resources\Inventory\InventoryItems\Tables\InventoryItemsTable;
use App\Models\Inventory\InventoryItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InventoryItemResource extends Resource
{
    protected static ?string $model = InventoryItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $recordTitleAttribute = 'productVariant.product.name';

    protected static string | UnitEnum | null $navigationGroup = NavigationItems::INVENTORY_MENU_GROUP;


    public static function form(Schema $schema): Schema
    {
        return InventoryItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryItemsTable::configure($table);
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
            'index' => ListInventoryItems::route('/'),
            // 'create' => CreateInventoryItem::route('/create'),
            // 'edit' => EditInventoryItem::route('/{record}/edit'),
        ];
    }
}
