<?php

namespace App\Filament\Resources\Order\Orders\Tables;

use App\Core\Order\OrderFulfillmentDomain;
use App\Filament\Resources\Order\Orders\OrderResource;
use App\Models\Order\Order;
use App\Models\Order\OrderStatus;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class OrderAction
{

    public static function getActions(): array
    {
        return [
            self::viewDetailsAction(),
            self::confirmOrderAction(),
            self::shipOrderAction(),
            self::deliverOrderAction(),
            // self::deliverOrderFailedAction(),
            self::cancelOrderAction(),
        ];
    }










    public static function cancelOrderAction(): Action
    {
        return Action::make('cancelOrder')
            ->visible(fn(Order $record): bool => in_array($record->status, [OrderStatus::PENDING, OrderStatus::CONFIRMED]))
            ->label('Cancel order')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription('Are you sure you want to cancel this order?')
            ->action(function (Order $record): void {
                OrderResource::authorizeEdit($record);

                try {
                    app(OrderFulfillmentDomain::class)->cancelOrder($record);
                } catch (DomainException $exception) {
                    Notification::make()->danger()->title('Order could not be canceled')->body($exception->getMessage())->send();

                    return;
                }

                Notification::make()->success()->title('Order canceled')->send();
            });
    }

    public static function viewDetailsAction(): Action
    {
        return ViewAction::make();
    }
    public static function confirmOrderAction(): Action
    {
        return Action::make('confirmOrder')
            ->visible(fn(Order $record): bool => in_array($record->status, [OrderStatus::PENDING]))

            ->label('Confirm order')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')

            // ->authorize(fn(Order $record): bool => OrderResource::canEdit($record))
            ->requiresConfirmation()
            ->modalDescription('Confirm this order for fulfillment?')
            ->action(function (Order $record): void {
                OrderResource::authorizeEdit($record);

                try {
                    app(OrderFulfillmentDomain::class)->confirmOrder($record);
                } catch (DomainException $exception) {
                    Notification::make()->danger()->title('Order could not be confirmed')->body($exception->getMessage())->send();

                    return;
                }

                Notification::make()->success()->title('Order confirmed')->send();
            });
    }
    public static function shipOrderAction(): Action
    {
        return Action::make('shipOrder')
            ->visible(fn(Order $record): bool => in_array($record->status, [OrderStatus::CONFIRMED]))

            ->label('Ship order')
            ->icon(Heroicon::OutlinedTruck)
            ->color('success')

            // ->authorize(fn(Order $record): bool => OrderResource::canEdit($record))
            ->requiresConfirmation()
            ->modalDescription('Confirm this order for fulfillment?')
            ->action(function (Order $record): void {
                OrderResource::authorizeEdit($record);

                try {
                    app(OrderFulfillmentDomain::class)->shipOrder($record);
                } catch (DomainException $exception) {
                    Notification::make()->danger()->title('Order could not be shipped')->body($exception->getMessage())->send();

                    return;
                }

                Notification::make()->success()->title('Order shipped')->send();
            });
    }
    public static function deliverOrderAction(): Action
    {
        return Action::make('deliverOrder')
            ->visible(fn(Order $record): bool => in_array($record->status, [OrderStatus::SHIPPED]))

            ->label('Mark as delivered')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')

            // ->authorize(fn(Order $record): bool => OrderResource::canEdit($record))
            ->requiresConfirmation()
            ->modalDescription('Confirm this order for fulfillment?')
            ->action(function (Order $record): void {
                OrderResource::authorizeEdit($record);

                try {
                    app(OrderFulfillmentDomain::class)->deliverOrder($record);
                } catch (DomainException $exception) {
                    Notification::make()->danger()->title('Order could not be marked as delivered')->body($exception->getMessage())->send();

                    return;
                }

                Notification::make()->success()->title('Order marked as delivered')->send();
            });
    }
    public static function deliverOrderFailedAction(): Action
    {
        return Action::make('deliverOrderFailed')
            ->label('Mark as delivered failed')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            // ->authorize(fn(Order $record): bool => OrderResource::canEdit($record))
            ->requiresConfirmation()
            ->modalDescription('Confirm this order for fulfillment?')
            ->action(function (Order $record): void {
                // OrderResource::authorizeEdit($record);

                // try {
                //     app(OrderFulfillmentDomain::class)->deliverOrder($record);
                // } catch (DomainException $exception) {
                //     Notification::make()->danger()->title('Order could not be marked as delivered')->body($exception->getMessage())->send();

                //     return;
                // }

                Notification::make()->success()->title('Order marked as delivered')->send();
            });
    }
}
