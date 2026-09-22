<?php

use App\Core\ProductCore;
use App\Models\Catalog\Product;

it('calculates the SKU correctly using the product slug', function () {
    $product = new Product([
        'id' => 15,
        'name' => 'Test Product',
        'slug' => 'special-item'
    ]);

    $core = new ProductCore();

    $sku = $core->calculateSku($product);

    expect($sku)->toBe('PRD-15-SPECIAL-ITEM');
});

it('calculates the SKU correctly using the product name if slug is missing', function () {
    $product = new Product([
        'id' => 42,
        'name' => 'Awesome Widget',
        'slug' => null
    ]);

    $core = new ProductCore();

    $sku = $core->calculateSku($product);

    expect($sku)->toBe('PRD-42-AWESOME-WIDGET');
});

it('calculates the SKU with fallback if name and slug are missing', function () {
    $product = new Product([
        'id' => 7,
        'name' => null,
        'slug' => null
    ]);

    $core = new ProductCore();

    $sku = $core->calculateSku($product);

    expect($sku)->toBe('PRD-7-');
});