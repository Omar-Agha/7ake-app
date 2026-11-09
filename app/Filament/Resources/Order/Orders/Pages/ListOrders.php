<?php

namespace App\Filament\Resources\Order\Orders\Pages;

use App\Filament\Resources\Order\Orders\OrderResource;
use App\Filament\Resources\Order\Orders\Widgets\OrdersOverview;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            OrdersOverview::class,
        ];
    }
}
