<?php

namespace App\Filament\Resources\Inventory\StockMovements\Pages;

use App\Core\Inventory\InventoryDomain;
use App\Filament\Resources\Inventory\StockMovements\StockMovementResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Mockery\Matcher\Not;

class ManageStockMovements extends ManageRecords
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {

        return [
            CreateAction::make()
                ->action(function (array $data, InventoryDomain $inventoryDomain, CreateAction $action) {
                    try {
                        $inventoryDomain->moveStock($data['product_variant_id'], $data['quantity'], $data['reason']);
                    } catch (\Throwable $th) {
                        Notification::make()
                            ->title('Error')
                            ->body($th->getMessage())
                            ->danger()
                            ->send();
                        $action->halt();
                    }
                }),
        ];
    }
    protected function mutatedata(array $data, InventoryDomain $inventoryDomain): Model
    {

        throw  new \DomainException('Stock movements cannot be created directly. Use the InventoryDomain to move stock.');
        return $inventoryDomain->moveStock($data['product_variant_id'], $data['quantity'], $data['reason']);
        // return static::getModel()::create($data);
    }
}
