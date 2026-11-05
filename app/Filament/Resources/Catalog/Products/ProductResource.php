<?php

namespace App\Filament\Resources\Catalog\Products;

use App\Enums\PublishStatus;
use App\Filament\Navigations\NavigationItems;
use App\Filament\Resources\Catalog\Products\Pages\CreateProduct;
use App\Filament\Resources\Catalog\Products\Pages\EditProduct;
use App\Filament\Resources\Catalog\Products\Pages\ListProducts;
use App\Filament\Resources\Catalog\Products\RelationManagers\VariantsRelationManager;
use App\Filament\Resources\Catalog\Products\Schemas\ProductForm;
use App\Filament\Resources\Catalog\Products\Tables\ProductsTable;
use App\Models\Catalog\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';
    protected static string | UnitEnum | null $navigationGroup = NavigationItems::CATALOG_MENU_GROUP;


    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
        ];
    }

    public static function getRecordActions(): array
    {
        return [
            Action::make("publish")
                ->visible(fn(Product $record): bool => in_array($record->product_status, [PublishStatus::Draft]))

                ->icon(Heroicon::PaperAirplane)
                ->requiresConfirmation()
                ->action(function (Product $record) {
                    $record->publish();
                    $record->save();
                }),


            Action::make("archive")
                ->visible(fn(Product $record): bool => in_array($record->product_status, [PublishStatus::Published]))
                ->icon(Heroicon::ArchiveBox)
                ->requiresConfirmation()
                ->action(function (Product $record) {
                    $record->archive();
                    $record->save();
                }),
            Action::make("unarchive")
                ->visible(fn(Product $record): bool => in_array($record->product_status, [PublishStatus::Archived]))
                ->icon(Heroicon::OutlinedArchiveBoxXMark)
                ->requiresConfirmation()
                ->action(function (Product $record) {
                    $record->unarchive();
                    $record->save();
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
