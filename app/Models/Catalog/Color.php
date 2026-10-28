<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    protected $fillable = [
        'name',
        'code',
        'hex_code',
        
    ];

    public function productVariants():HasMany{
        return $this->hasMany(ProductVariant::class);
    }
}
