<?php

namespace App\Models\Catalog;

use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Product extends Model
{
    use HasFactory;
    use \App\Traits\HasImages;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'product_status',
        'category_id'
    ];

    protected $casts = [
        'product_status' => \App\Enums\PublishStatus::class,
    ];

    /**
     * Get the category that the product belongs to.
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the product variants for the product.
     */
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * The product collections that this product belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productCollections(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductCollection::class);
    }

    /**
     * Get the product along with its variants and inventory quantities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productVariantsWithInventory(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->with('inventory');
    }

    /**
     * Get all product variants' inventories for the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function inventories():HasManyThrough
    {
        return $this->hasManyThrough(
            InventoryItem::class,
            \App\Models\Catalog\ProductVariant::class,
            'product_id', // Foreign key on ProductVariant table
            'product_variant_id', // Foreign key on Inventory table
            'id', // Local key on Product table
            'id' // Local key on ProductVariant table
        );
    }

    /**
     * Mark the product as the given status.
     *
     * @param \App\Enums\ProductStatus $status
     * @return void
     */
    public function markStatus(\App\Enums\PublishStatus $status): void
    {
        $this->product_status = $status;
        $this->save();
    }


    /**
     * Mark the product as published.
     *
     * @return void
     */
    public function markPublish(): void
    {
        $this->product_status = \App\Enums\PublishStatus::Published;
        $this->save();
    }



    /**
     * Mark the product as archived.
     *
     * @return void
     */
    public function markArchived(): void
    {
        $this->product_status = \App\Enums\PublishStatus::Archived;
        $this->save();
    }
}
