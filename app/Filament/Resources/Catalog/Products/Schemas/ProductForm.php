<?php

namespace App\Filament\Resources\Catalog\Products\Schemas;

use App\Enums\PublishStatus;
use App\Filament\Forms\Components\ImageGallery;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
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

                Select::make('productCollections')
                    ->relationship('productCollections', 'name') // First arg is the relationship name, second is the column to display
                    ->multiple() // Enables multi-choice selection
                    ->preload()  // Optional: Loads options ahead of time for a smoother UX
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        Select::make('status')
                            ->options(PublishStatus::class)
                            ->default(PublishStatus::Draft)
                            ->disabled()
                            ->required(),
                    ]),
                    ImageGallery::make(),
               
            ]);
    }
}
