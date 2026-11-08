<?php

namespace App\Core\Product;

use App\Enums\ProductSize;
use App\Models\Catalog\Color;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Support\Str;

class ProductDomain
{

    public function generateVariantName(ProductSize $size, Color $color): string
    {
        return Str::ucfirst($color->code) . '/' . $size->skuCode();
    }

    public function generateSkuCode(ProductSize $size, Product $product, Color $color): string
    {
        $product_code = sprintf('%03d', $product->id);
        return "7K-H{$product_code}-{$color->code}-{$size->skuCode()}";
    }

    public function deleteProductVariant(ProductVariant $productVariant): void
    {
        // throw new \DomainException("Cannot delete product variant with existing inventory items. Please settle inventory before deleting the product variant.");
        if ($productVariant->inventory()
            ->where('quantity', '>', 0)
            ->orWhere('reserved_quantity', '>', 0)->exists()
        ) {
            // throw new \DomainException("Cannot delete product variant with existing inventory items. Please settle inventory before deleting the product variant.");
            throw new \DomainException(__("app.product.delete_product_variant_with_inventory"));
        }
        $productVariant->delete();
    }
}
