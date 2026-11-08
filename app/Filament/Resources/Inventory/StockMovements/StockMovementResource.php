<?php

namespace App\Filament\Resources\Inventory\StockMovements;

use App\Core\Inventory\InventoryDomain;
use App\Enums\StockMovementReason;
use App\Filament\Navigations\NavigationItems;
use App\Filament\Resources\Inventory\StockMovements\Pages\ManageStockMovements;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductCollection;
use App\Models\Inventory\StockMovement;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $recordTitleAttribute = 'inventoryItem.productVariant.product.name';
    protected static string | UnitEnum | null $navigationGroup = NavigationItems::INVENTORY_MENU_GROUP;

    public static function formFields()
    {
        return [
            Select::make('product_id')
                ->label('Product')
                ->options(fn() => Product::query()->pluck('name', 'id'))
                ->live()
                ->dehydrated(false)
                ->required(),
            Select::make('product_variant_id')
                ->label('Variant')
                ->options(fn(Get $get) => $get('product_id')
                    ? Product::find($get('product_id'))->productVariants->pluck('name', 'id')
                    : [])
                ->disabled(fn(Get $get) => blank($get('product_id')))
                ->required(),
            TextInput::make('quantity')
                ->required()
                ->numeric(),

            Select::make('reason')
                ->options(StockMovementReason::class)
                ->required(),

            // TextInput::make('movement_type'),

        ];
    }



    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...self::formFields()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('inventoryItem.productVariant.product.name')
            ->modifyQueryUsing(fn($query) => $query->with('inventoryItem.productVariant.product'))
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('inventoryItem.productVariant.product.name')
                    ->sortable(),
                TextColumn::make('inventoryItem.productVariant.name')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reason')
                    ->badge()
                    ->searchable(),
                // TextColumn::make('reference_type')
                //     ->searchable(),
                // TextColumn::make('reference_id')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('movement_type')
                //     ->searchable(),
            ])
            ->filters([
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

                                    return $query->whereHas('inventoryItem.productVariant.product', function (Builder $query) use ($productIds) {
                                        $query->whereIn('products.id', $productIds);
                                    });
                                }
                            }
                        )
                        ->when(
                            $data['product_id'] ?? null,
                            function (Builder $query, $id) {
                                $product_variants = Product::find($id)?->productVariants()->pluck('product_variants.id')->toArray() ?? [];
                                return $query->whereHas('inventoryItem', fn(Builder $query) => $query->whereIn('product_variant_id', $product_variants));
                                // return $query->whereIn('inventoryItem.product_variant_id', $product_variants);
                            }
                        );
                })
            ], layout: FiltersLayout::AboveContent)->deferFilters(false)
            ->recordActions([
                // EditAction::make(),
                // DeleteAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStockMovements::route('/'),
        ];
    }
}
