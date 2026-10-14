<?php

use App\Core\Inventory\InventoryDomain;
use App\Models\Inventory\InventoryItem;
use Carbon\Carbon;

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});
