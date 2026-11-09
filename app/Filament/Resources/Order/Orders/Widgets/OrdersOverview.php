<?php

namespace App\Filament\Resources\Order\Orders\Widgets;

use App\Models\Order\Order;
use App\Models\Order\OrderStatus;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [

            Stat::make('Pending Orders', Order::where('status', OrderStatus::PENDING)->count())

                ->description('Awaiting confirmation')

                ->color('warning'),

            Stat::make('Confirmed Orders', Order::where('status', OrderStatus::CONFIRMED)->count())

                ->description('Ready for shipping')

                ->color('info'),

            Stat::make('Shipped Orders', Order::where('status', OrderStatus::SHIPPED)->count())

                ->description('In delivery')

                ->color('primary'),

            Stat::make('Delivered Orders', Order::where('status', OrderStatus::DELIVERED)->count())

                ->description('Successfully delivered')

                ->color('success'),

        ];
    }
}
