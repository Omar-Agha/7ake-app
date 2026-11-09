<?php

namespace App\Filament\Resources\Order\Orders\Tables;

use App\Core\Order\OrderFulfillmentDomain;
use App\Filament\Resources\Order\Orders\OrderResource;
use App\Models\Order\Order;
use App\Models\Order\OrderStatus;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query
                ->with('customer')
                ->withSum('orderItems', 'quantity'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order number')
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->state(function (Order $record): string {
                        $customer = $record->customer;

                        return trim($customer->f_name . ' ' . $customer->l_name)
                            ?: ($customer->email ?: ($customer->phone ?: 'Unknown customer'));
                    })
                    ->searchable(query: fn(Builder $query, string $search): Builder => $query
                        ->whereHas('customer', fn(Builder $query): Builder => $query
                            ->where('f_name', 'like', "%{$search}%")
                            ->orWhere('l_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"))),
                TextColumn::make('status')
                    ->label('Order status')
                    ->state(fn(Order $record): string => (self::status($record))->name ?? 'UNKNOWN')
                    ->formatStateUsing(fn(string $state): string => ucfirst(strtolower($state)))
                    ->badge()
                    ->color(fn(Order $record): string => match (self::status($record)) {
                        OrderStatus::PENDING => 'warning',
                        OrderStatus::CONFIRMED, OrderStatus::SHIPPED => 'info',
                        OrderStatus::DELIVERED => 'success',
                        OrderStatus::CANCELLED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('payment_status_display')
                    ->label('Payment status')
                    ->state(fn(): string => 'Unavailable')
                    ->badge()
                    ->color('gray')
                    ->tooltip('Payment records do not yet have order or status fields.'),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money(fn(Order $record): string => strtoupper($record->currency))
                    ->sortable(),
                TextColumn::make('order_items_sum_quantity')
                    ->label('Items')
                    ->tooltip('Total units ordered')
                    ->default(0)
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Order status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(
                        fn(OrderStatus $status): array => [$status->value => ucfirst(strtolower($status->name))],
                    )->all())
                    ->query(function (Builder $query, array $data): Builder {
                        if (! filled($data['value'] ?? null)) {
                            return $query;
                        }

                        $status = OrderStatus::tryFrom((int) $data['value']);

                        return $status ? $query->whereIn('status', [(string) $status->value, strtolower($status->name)]) : $query;
                    }),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label('Created from'),
                        DatePicker::make('until')->label('Created until'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn(Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn(Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->recordUrl(null)
            ->modifyUngroupedRecordActionsUsing(fn(Action $action) => $action->iconButton()->tooltip(fn(Action $action): string => $action->getName()))
            ->recordActions(OrderAction::getActions())
            ->emptyStateHeading('No orders found')
            ->emptyStateDescription('Orders will appear here after checkout. Try adjusting your search or filters.');
    }

    /**
     * The schema defaults to text statuses, while checkout stores integer enum values.
     */
    private static function status(Order $record): ?OrderStatus
    {
        $value = $record->getRawOriginal('status');

        foreach (OrderStatus::cases() as $status) {
            if ((string) $value === (string) $status->value || strtolower((string) $value) === strtolower($status->name)) {
                return $status;
            }
        }

        return null;
    }
}
