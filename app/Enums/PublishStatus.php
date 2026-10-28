<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PublishStatus: int implements HasLabel,HasColor
{
    case Draft = 1;
    case Published = 2;
    case Archived = 3;

    public function getLabel(): string | Htmlable | null
    {
        return $this->name;
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Archived => 'warning',
            self::Published => 'success',
            
        };
    }
}