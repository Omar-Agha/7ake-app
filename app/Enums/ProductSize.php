<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProductSize: int implements HasLabel//, HasColor
{
    case Small = 1;
    case Medium = 2;
    case Large = 3;
    case ExtraLarge = 4;

    public function getLabel(): string | Htmlable | null
    {
        return $this->name;
    }

    // public function getColor(): string | array | null
    // {
    //     return match ($this) {
    //         self::Small => 'gray',
    //         self::Archived => 'warning',
    //         self::Published => 'success',
    //     };
    // }

    public function skuCode(): string

    {

        return match ($this) {

            self::Small => 'S',

            self::Medium => 'M',

            self::Large => 'L',
            self::ExtraLarge => 'XL',
        };
    }
}
