<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StockMovementReason: int implements HasLabel, HasColor
{
    case Purchase = 1;             // Inventory moved in due to a purchase from supplier
    case Sale = 2;                 // Inventory moved out due to a sale/order
    case ReturnFromCustomer = 3;   // Customer returned product, inventory moved back in
    case ReturnToSupplier = 4;     // Sent product back to supplier, inventory moved out
    case Adjustment = 5;           // Manual adjustment (e.g. physical count, corrections)
    case Damage = 6;               // Inventory loss due to damaged goods
    case Transfer = 7;             // Inventory moved between warehouse locations
    case Other = 8;


    public function getLabel(): string | Htmlable | null
    {
        return $this->name;
    } // Miscellaneous or custom reason

    public function getColor(): string | array | null
    {

        return match ($this) {
            self::Purchase => 'success',
            self::Sale => 'danger',
            self::ReturnFromCustomer => 'primary',
            self::ReturnToSupplier => 'warning',
            self::Adjustment => 'secondary',
            self::Damage => 'danger',
            self::Transfer => 'info',
            self::Other => 'gray',
        };
    }
}
