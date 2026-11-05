<?php

namespace App\Support\Macros;

use Closure;
use Illuminate\Support\Str;

class TextColumnMixin
{
    /**
     * Display English and Arabic translations side-by-side with an optional word limit.
     */
    public function bilingual(): Closure
    {
        return function (?int $words = null) {
            /** @var \Filament\Tables\Columns\TextColumn $this */
            $attribute = $this->getName();

            return $this->formatStateUsing(function ($record) use ($attribute, $words) {
                    $en = method_exists($record, 'getTranslation') ? $record->getTranslation($attribute, 'en') : '';
                    $ar = method_exists($record, 'getTranslation') ? $record->getTranslation($attribute, 'ar') : '';

                    // Safely truncate words independently for both languages if a limit is passed
                    if ($words) {
                        $en = $en ? Str::words($en, $words) : '';
                        $ar = $ar ? Str::words($ar, $words) : '';
                    }

                    return '<div class="font-bold">' . e($en) . '</div>' .
                           '<div class="text-xs text-gray-500">' . e($ar) . '</div>';
                })
                ->html();
        };
    }
}
