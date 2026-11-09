<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Cart\CartApiController;
use App\Http\Controllers\Catalog\ProductApiController;
use App\Models\Catalog\Product;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    // Return paginated users, ordered by the latest created.

    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix' => 'catalog'], function () {
    Route::get('/products', [ProductApiController::class, 'index']);
    Route::get('/products/{slug}', [ProductApiController::class, 'show']);
    Route::get('/collections', [ProductApiController::class, 'getCollections']);
    Route::get('/upcoming-collections', [ProductApiController::class, 'getUpcomingCollections']);
    Route::get('/collections/{slug}', [ProductApiController::class, 'getCollectionBySlug']);
    Route::get('/categories/', [ProductApiController::class, 'getCategories']);
});
Route::group(['prefix' => 'customer-auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::group(['prefix' => 'cart'], function () {
    Route::get('/', [CartApiController::class, 'getCart']);
    Route::post('/items', [CartApiController::class, 'addOrUpdateItem']);
    Route::delete('/items/{id}', [CartApiController::class, 'deleteItemFromCart']);
})->middleware(['auth:sanctum', 'customer']);

// Route::group(['prefix' => 'order'], function () {
//     Route::post('/checkout',);
// })->middleware(['auth:sanctum', 'customer']);



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
