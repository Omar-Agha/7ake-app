<?php

namespace App\Filament\Resources\Order\Orders;

use App\Filament\Navigations\NavigationItems;
use App\Filament\Resources\Order\Orders\Pages\ListOrders;
use App\Filament\Resources\Order\Orders\Pages\ViewOrder;
use App\Filament\Resources\Order\Orders\Schema\OrderInfolist;
use App\Filament\Resources\Order\Orders\Tables\OrdersTable;
use App\Filament\Resources\Order\Orders\Widgets\OrdersOverview;
use App\Models\Order\Order;
use App\Models\Order\OrderStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $recordTitleAttribute = 'order_number';

    protected static string|UnitEnum|null $navigationGroup = NavigationItems::ORDER_MENU_GROUP;

    public static function getNavigationBadge(): ?string
    {
        return (string) Order::query()->where('status', OrderStatus::PENDING)->count();
    }


    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }
    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),

        ];
    }
}
