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
    return Product::withCount('productVariants')->get();
    
    

});
