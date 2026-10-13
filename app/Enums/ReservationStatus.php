<?php

namespace App\Enums;

enum ReservationStatus: int
{
    case Active = 1;       // Reservation is currently active and inventory is reserved
    case Released = 2;     // Reservation was cancelled or manually released
    case Expired = 3;      // Reservation expired and inventory was returned to available
    case Completed = 4;    // Reservation fulfilled (e.g., sale completed, inventory taken)
}