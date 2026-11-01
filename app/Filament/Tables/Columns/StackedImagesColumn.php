<?php

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class StackedImagesColumn
{
    public static function make(string $name = 'images.path', string $label = 'Images') : ImageColumn
    {
        return ImageColumn::make($name)
            ->label($label)
            ->circular()
            ->stacked()
            ->limit(3)
            ->limitedRemainingText(size: 'xs')
            ->disk('public'); // Ensure this matches the disk where your FileUpload stores images

    }
}
