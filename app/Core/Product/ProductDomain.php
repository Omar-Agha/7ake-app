<?php

namespace App\Core\Product;

use App\Models\Catalog\Color;
use App\Models\Catalog\ProductVariant;
use Illuminate\Support\Str;

class ProductDomain
{

    public function generateVariantName(ProductVariant $variant,Color $color):string {
        return Str::ucfirst($color->code) . '/' . $variant->product_size->skuCode();
    }

    public function generateSkuCode(ProductVariant $variant,Color $color):string {
        return "go";
    }

}
