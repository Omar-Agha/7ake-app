<?php

namespace App\Listeners;

use App\Core\Inventory\InventoryDomain;
use App\Events\ProductVariantWasCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateInitialInventoryForProduct
{
    /**
     * Create the event listener.
     */
    public function __construct(private InventoryDomain $inventoryDomain)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProductVariantWasCreated $event): void
    {
        $this->inventoryDomain->initializeInventoryForProductVariant($event->productVariant->id);
    }
}
