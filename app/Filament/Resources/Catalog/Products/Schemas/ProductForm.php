<?php

namespace App\Filament\Resources\Catalog\Products\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Enums\PublishStatus;
use App\Filament\Forms\Components\ImageGallery;
use App\Filament\Resources\Catalog\Categories\CategoryResource;
use App\Filament\Resources\Catalog\ProductCollections\ProductCollectionResource;
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
                TranslatableTabs::make('')->schema([
                    TextInput::make('name')
                        ->required(),

                    Textarea::make('description')
                        ->columnSpanFull(),
                ])->columnSpanFull(),
                Select::make('product_status')
                    ->options(PublishStatus::class)
                    ->default(PublishStatus::Draft)
                    ->disabled()
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->createOptionForm([
                        ...CategoryResource::formFields()
                    ]),

                Select::make('productCollections')
                    ->relationship('productCollections', 'name') // First arg is the relationship name, second is the column to display
                    ->multiple() // Enables multi-choice selection
                    ->preload()  // Optional: Loads options ahead of time for a smoother UX
                    ->searchable()
                    ->createOptionForm([...ProductCollectionResource::formFields()]),
                ImageGallery::make(),

            ]);
    }
}
