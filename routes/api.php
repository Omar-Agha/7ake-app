<?php

use App\Models\Catalog\Product;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    // Return paginated users, ordered by the latest created.

    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hi', function (Request $request) {
    app()->setLocale('ar');
    return Product::with('productCollections')->get();
    // $p = new Product();
    // $p->setTranslation('name', 'en', 'Product 1');
    // $p->setTranslation('name', 'ar', 'Product 1 ar');
    // $p->setTranslation('description', 'en', 'Description 1');
    // $p->setTranslation('description', 'ar', 'Description 1');
    // return $p->save();
    return Product::withCount('productVariants')->get();
});
