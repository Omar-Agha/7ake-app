<?php

namespace App\Models\Order;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum OrderStatus: int implements HasLabel, HasColor
{
    case PENDING = 0;
    case CONFIRMED = 1;
    case SHIPPED = 2;
    case DELIVERED = 3;
    case CANCELLED = 4;
    case RETURNED = 5;

    public function getLabel(): string | Htmlable | null
    {
        return $this->name;
    }

    public function getColor(): string | array | null
    {

        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::SHIPPED => 'primary',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::RETURNED => 'gray',
        };
    }
}
