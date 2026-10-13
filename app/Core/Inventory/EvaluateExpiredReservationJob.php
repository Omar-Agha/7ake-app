<?php

namespace App\Core\Inventory;

use App\Models\Inventory\Reservation;


use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Command to evaluate and process expired inventory reservations.
 */
class EvaluateExpiredReservationJob implements ShouldQueue
{
  
    /**
     * Invokes the evaluation and processing of expired inventory reservations.
     */
    public function handle(InventoryDomain $inventoryDomain): void
    {
        $now = Carbon::now();

        $expiredReservations = Reservation::where('expires_at', '<', $now)
            ->where('status', 'active')
            ->get();

        foreach ($expiredReservations as $reservation) {
            $inventoryDomain->expireReservationIfNeeded($reservation);
        }
    }
}