<?php

namespace App\Filament\Resources\Inventory\InventoryItems\Pages;

use App\Filament\Resources\Inventory\InventoryItems\InventoryItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInventoryItem extends EditRecord
{
    protected static string $resource = InventoryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
