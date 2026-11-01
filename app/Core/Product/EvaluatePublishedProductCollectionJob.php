<?php

namespace App\Core\Product;

use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Command to evaluate and process expired inventory reservations.
 */
class EvaluatePublishedProductCollectionJob implements ShouldQueue
{
  
}