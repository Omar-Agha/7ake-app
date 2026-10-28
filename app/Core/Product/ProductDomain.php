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
}
