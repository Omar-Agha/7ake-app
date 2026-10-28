<?php

namespace App\Filament\Resources\Catalog\Products\Schemas;

use App\Enums\PublishStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('product_status')
                    ->options(PublishStatus::class)
                    ->default(PublishStatus::Draft)
                    ->disabled()
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name'),
            ]);
    }
}
