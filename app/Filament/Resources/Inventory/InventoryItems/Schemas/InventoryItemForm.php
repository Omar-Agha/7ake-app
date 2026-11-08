<?php

namespace App\Filament\Resources\Inventory\InventoryItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_variant_id')
                    ->relationship('productVariant', 'name')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('reserved_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
