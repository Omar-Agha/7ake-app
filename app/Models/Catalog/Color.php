<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = [
        'name',
        'code',
        'hex_code',
        
    ];
}
