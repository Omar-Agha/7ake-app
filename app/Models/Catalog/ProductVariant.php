<?php

namespace App\Models\Catalog;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    use \App\Traits\HasImages;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        
        'product_id', 'name', 'price', 'sku','color','product_size'
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'product_size' => \App\Enums\ProductSize::class,
    ];

    /**
     * Get the inventory for the product variant.
     */
    public function inventory():HasOne
    {
        return $this->hasOne(Inventory::class);
    }
}
