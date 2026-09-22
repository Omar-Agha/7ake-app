<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('/inline', function () {
    
    return 'This is an inline GET route.';
})->name('inline');

