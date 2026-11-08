<?php

namespace App\Filament\Resources\Inventory\InventoryItems\Pages;

use App\Filament\Resources\Inventory\InventoryItems\InventoryItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryItem extends CreateRecord
{
    protected static string $resource = InventoryItemResource::class;
}
