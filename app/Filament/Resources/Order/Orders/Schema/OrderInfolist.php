<?php

namespace App\Filament\Resources\Order\Orders\Schema;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Order overview')
                    ->description('Status and key details for this order.')
                    ->icon('heroicon-o-shopping-bag')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')->label('Order ID')->placeholder('—'),
                        TextEntry::make('order_number')->label('Order number')->placeholder('—'),
                        TextEntry::make('status')->badge()->placeholder('—'),
                        TextEntry::make('payment_status')->label('Payment status')->badge()->placeholder('—'),
                        TextEntry::make('payment_method')->label('Payment method')->placeholder('—'),
                        TextEntry::make('currency')->label('Currency')->placeholder('—'),
                        TextEntry::make('created_at')->label('Placed')->dateTime()->placeholder('—'),

                    ]),

                Section::make('Customer and delivery')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('customer.f_name')->state(fn($record) => $record->customer->fullName())->label('Customer')->placeholder('—'),
                        TextEntry::make('customer.email')->label('Email')->copyable()->placeholder('—'),
                        TextEntry::make('customer.phone')->label('Phone')->placeholder('—'),
                        TextEntry::make('shipping_address')->label('Shipping address')->columnSpanFull()->placeholder('—'),

                    ]),

                Section::make('Items')
                    ->description('Products and quantities captured when the order was placed.')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        RepeatableEntry::make('orderItems')
                            ->label('Order items')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('product_name')->label('Product')->weight('bold'),
                                TextEntry::make('sku')->label('SKU')->placeholder('—'),
                                TextEntry::make('productVariant.images.path'),
                                TextEntry::make('quantity')->numeric(),
                                TextEntry::make('price')->label('Unit price')->numeric(decimalPlaces: 2),
                                TextEntry::make('total')->label('Line total')->numeric(decimalPlaces: 2),
                                TextEntry::make('currency')->label('Currency'),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Totals')
                    ->icon('heroicon-o-banknotes')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('sub_total')->numeric(decimalPlaces: 2)->placeholder('—'),
                        TextEntry::make('discount_amount')->numeric(decimalPlaces: 2)->placeholder('—'),
                        // TextEntry::make('tax')->numeric(decimalPlaces: 2)->placeholder('—'),
                        TextEntry::make('shipping_amount')->label('Shipping')->numeric(decimalPlaces: 2)->placeholder('—'),
                        TextEntry::make('total_amount')->numeric(decimalPlaces: 2)->weight('bold')->placeholder('—'),
                    ]),

                Section::make('Notes')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        TextEntry::make('notes')->hiddenLabel()->placeholder('No notes')->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
