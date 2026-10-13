<?php

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    // Return paginated users, ordered by the latest created.

    return $request->user();
})->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    
    $result = Cache::block(10, function () {
        // This logic executes only if the lock is acquired within 10s (or immediately if available).
        return App\Models\User::find(3);
    });
    $user = \Cache::remember('go', now()->addMinutes(60), function () {
        // Use a lock to prevent cache stampede
        $lock = \Cache::lock('go_lock', 10); // lock for 10 seconds max

        try {
            if ($lock->get()) {
                // Only one process will hit DB and recache
                return \App\Models\User::find(3);
            } else {
                // If lock can't be acquired quickly, wait for cache to be filled
                $lock->block(3); // wait up to 3 seconds
                return \Cache::get('go');
            }
        } finally {
            optional($lock)->release();
        }
    });

    return $request->user();
})->middleware('auth:sanctum');
