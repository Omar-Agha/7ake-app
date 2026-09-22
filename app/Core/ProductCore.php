<?php

namespace App\Core;

use App\Models\Catalog\Product;

class ProductCore
{
    // Product core logic will be implemented here
    /**
     * Calculates the SKU for a given Product.
     *
     * @param \App\Models\Product $product
     * @return string
     */
    public function calculateSku(Product $product): string
    {
        // You can adjust the SKU logic as needed.
        // Example logic: SKU = PRD-{id}-{slug/uppercase}
        $id = $product->id;
        $slug = strtoupper($product->slug ?? str_replace(' ', '-', $product->name ?? ''));
        return "PRD-{$id}-{$slug}";
    }
}