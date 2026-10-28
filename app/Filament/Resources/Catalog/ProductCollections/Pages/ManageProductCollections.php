<?php

namespace App\Filament\Resources\Catalog\ProductCollections\Pages;

use App\Filament\Resources\Catalog\ProductCollections\ProductCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProductCollections extends ManageRecords
{
    protected static string $resource = ProductCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
