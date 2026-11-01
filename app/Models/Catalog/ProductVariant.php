<?php

namespace App\Models\Catalog;

use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int|null $product_id
 * @property string $name
 * @property numeric $price
 * @property string|null $sku
 * @property \App\Enums\ProductSize|null $product_size
 * @property int $color_id
 * @property-read \App\Models\Catalog\Color $color
 * @property-read mixed $primary_image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $images
 * @property-read int|null $images_count
 * @property-read InventoryItem|null $inventory
 * @property-read \App\Models\Catalog\Product|null $product
 * @method static \Database\Factories\Catalog\ProductVariantFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereColorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereProductSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductVariant extends Model
{
    use HasFactory;
    use \App\Traits\HasImages;
    /**
     * The attributes that are mass assignable./////
     *
     * @var array<int, string>
     */
    protected $fillable = [
        
        'product_id', 'name', 'price', 'sku','product_size','color_id'
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
        return $this->hasOne(InventoryItem::class);
    }

    /**
     * Get the product that this variant belongs to.
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color():BelongsTo{
        return $this->belongsTo(Color::class);
    }
}
