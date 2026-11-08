<?php

namespace App\Filament\Resources\Inventory\InventoryItems\Pages;

use App\Filament\Resources\Inventory\InventoryItems\InventoryItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventoryItems extends ListRecords
{
    protected static string $resource = InventoryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
