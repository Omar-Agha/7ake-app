<?php

namespace App\Enums;


enum StockMovementReason: int
{
    case Purchase = 1;             // Inventory moved in due to a purchase from supplier
    case Sale = 2;                 // Inventory moved out due to a sale/order
    case ReturnFromCustomer = 3;   // Customer returned product, inventory moved back in
    case ReturnToSupplier = 4;     // Sent product back to supplier, inventory moved out
    case Adjustment = 5;           // Manual adjustment (e.g. physical count, corrections)
    case Damage = 6;               // Inventory loss due to damaged goods
    case Transfer = 7;             // Inventory moved between warehouse locations
    case Other = 8;                // Miscellaneous or custom reason
}