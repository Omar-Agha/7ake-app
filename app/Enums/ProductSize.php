<?php

namespace App\Enums;

enum ProductSize: int
{
    case Small = 1;
    case Medium = 2;
    case Large = 3;
    case ExtraLarge = 4;

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
