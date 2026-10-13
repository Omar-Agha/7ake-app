<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Category extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Add your fillable attributes here, for example:
        'name', 'slug', 'description',
    ];

    /**
     * Get the products for the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }
}
