<?php

namespace App\Filament\Resources\Inventory\InventoryItems\Tables;

use App\Core\Inventory\InventoryConsts;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductCollection;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['productVariant.product']))

            ->columns([


                TextColumn::make('productVariant.product.name')
                    ->searchable(),
                TextColumn::make('productVariant.name')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->color(fn($state): ?string => $state < InventoryConsts::INVENTORY_COUNT_DANGER ? 'danger' : null)
                    ->sortable(),
                TextColumn::make('reserved_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->numeric(),

            ])
            ->filters([
                SelectFilter::make("stock_status")
                    ->options([
                        'empty' => 'Empty',
                        'contains_reserved' => 'Reserved',
                    ])->query(function (Builder $query, array $data): Builder {

                        return match ($data['value'] ?? null) {
                            'empty' => $query->where('quantity', 0),
                            'contains_reserved' => $query->where('reserved_quantity', '>', 0),
                            default =>  $query,
                        };
                    }),
                Filter::make('product_filter')->schema([
                    Select::make('product_collection_id')
                        ->label(__('app.product.collection'))
                        ->options(fn() => ProductCollection::query()->pluck('name', 'id'))
                        ->live(),
                    Select::make('product_id')
                        ->label(__('app.product.product'))
                        ->options(fn(Get $get) => Product::when($get('product_collection_id'), fn(Builder $query) => $query->whereRelation('productCollections', 'product_collections.id', $get('product_collection_id')))->pluck('name', 'id'))
                ])->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['product_collection_id'] ?? null,
                            function (Builder $query, $id) {
                                $collection = ProductCollection::find($id);

                                if ($collection) {
                                    $productIds = $collection->products()->pluck('products.id')->toArray();

                                    return $query->whereHas('productVariant.product', function (Builder $query) use ($productIds) {
                                        $query->whereIn('products.id', $productIds);
                                    });
                                }
                            }
                        )
                        ->when(
                            $data['product_id'] ?? null,
                            function (Builder $query, $id) {
                                $product_variants = Product::find($id)?->productVariants()->pluck('product_variants.id')->toArray() ?? [];
                                return $query->whereIn('product_variant_id', $product_variants);
                            }
                        );
                })



            ], layout: FiltersLayout::AboveContent)->deferFilters(false)
            ->recordActions([
                // EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
