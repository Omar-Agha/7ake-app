<?php

namespace App\Filament\Resources\Catalog\Products\RelationManagers;

use App\Core\Product\ProductDomain;
use App\Enums\ProductSize;
use App\Models\Catalog\Color;
use App\Models\Catalog\ProductVariant;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'productVariants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->required()
                    ->suffixAction(
                        Action::make('generateName')
                            ->icon('heroicon-m-sparkles')
                            ->tooltip('Generate random name')
                            ->action(function (Set $set, ProductVariant $record, ProductDomain $domain, Get $get) {
                                // Your generation logic here (e.g., random string, service call, etc.)
                                $color = Color::find($get('color_id'));
                                $set('name', $domain->generateVariantName($record, $color));
                            })
                    ),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0.01)
                    ->prefix('$'),
                TextInput::make('sku')
                    ->label('SKU'),

                Select::make('color_id')
                    ->live()
                    ->required()
                    ->relationship('color', 'name')
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('code')->required()->maxLength(255),
                        ColorPicker::make('hex_code')->required(),
                    ]),
                Select::make('product_size')
                    ->required()
                    ->default(ProductSize::Small)
                    ->selectablePlaceholder(false)
                    ->options(ProductSize::class),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('price')
                    ->money()

                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('color.name')
                    ->searchable(),
                TextColumn::make('product_size')
                    ->badge()

                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),

            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
