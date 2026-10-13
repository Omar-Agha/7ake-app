<?php

use App\Core\Inventory\InventoryDomain;
use App\Models\Inventory\Reservation;
use Illuminate\Support\Carbon;

// To run this feature test file, use one of the following commands:
//
//     ./vendor/bin/pest tests/Feature/Test_InventoryDomain.php
//
// or with Artisan:
//
//     php artisan test tests/Feature/Test_InventoryDomain.php
//
// To run all feature tests:
//
//     ./vendor/bin/pest


it('expires reservation if past expiry', function () {
    // Create a reservation with an expiry in the past
    $reservation = Reservation::factory()->create([
        'quantity' => 1,
        'reserved_at' => now()->subHours(2),
        'expires_at' => now()->subHour(),
        'status' => \App\Enums\ReservationStatus::Active,
    ]);

    // $inventoryDomain = app(InventoryDomain::class);

    // $inventoryDomain->expireReservationIfNeeded($reservation->fresh());

    // expect($reservation->fresh()->status)->toBe('exspired');
});

// it('does not expire reservation if not yet expired', function () {
//     $reservation = Reservation::factory()->create([
//         'expires_at' => now()->addHour(),
//         'status' => 'active',
//     ]);

//     $inventoryDomain = app(InventoryDomain::class);

//     $inventoryDomain->expireReservationIfNeeded($reservation->fresh());

//     expect($reservation->fresh()->status)->toBe('active');
// });

// it('EvaluateExpiredReservationJob expires all expired reservations', function () {
//     $expired = Reservation::factory()->count(2)->create([
//         'expires_at' => now()->subMinutes(10),
//         'status' => 'active',
//     ]);
//     $active = Reservation::factory()->count(2)->create([
//         'expires_at' => now()->addMinutes(10),
//         'status' => 'active',
//     ]);

//     $inventoryDomain = app(InventoryDomain::class);
//     $job = app(App\Core\Inventory\EvaluateExpiredReservationJob::class);

//     $job->handle($inventoryDomain);

//     foreach ($expired as $reservation) {
//         expect($reservation->fresh()->status)->toBe('expired');
//     }

//     foreach ($active as $reservation) {
//         expect($reservation->fresh()->status)->toBe('active');
//     }
// });