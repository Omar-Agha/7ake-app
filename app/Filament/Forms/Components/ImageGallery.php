<?php
namespace App\Filament\Forms\Components;


use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Section;

class ImageGallery
{
    public static function make(string $relationship = 'images', string $directory = 'uploads'): Section
    {
        return Section::make('Images')
            ->description('Manage and reorder photos. Set one image as primary.')
            ->schema([
                Repeater::make($relationship)
                    ->relationship($relationship)
                    ->schema([
                        FileUpload::make('path')
                            ->label('Image')
                            ->image()
                            ->imageEditor()
                            ->directory($directory) // Interchangeable directory passed from outside
                            ->required()
                            ->columnSpanFull()
                            ->disk('public'),

                        Checkbox::make('is_primary')
                            ->label('Primary Image')
                            ->default(false),
                    ])
                    ->orderColumn('sort')
                    ->reorderable()
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->grid([
                        'default' => 1,
                        'md' => 2,
                        'lg' => 3,
                    ])
                    ->itemLabel(fn (array $state): ?string => isset($state['is_primary']) && $state['is_primary'] ? 'Primary Image' : 'Image Item')
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

}