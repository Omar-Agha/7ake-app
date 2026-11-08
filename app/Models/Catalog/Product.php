<?php

namespace App\Models\Catalog;

use App\Enums\PublishStatus;
use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string $name
 * @property string|null $description
 * @property PublishStatus $product_status
 * @property int|null $category_id
 * @property-read \App\Models\Catalog\Category|null $category
 * @property-read mixed $primary_image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalog\ProductCollection> $productCollections
 * @property-read int|null $product_collections_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalog\ProductVariant> $productVariants
 * @property-read int|null $product_variants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalog\ProductVariant> $productVariantsWithInventory
 * @property-read int|null $product_variants_with_inventory_count
 * @method static \Database\Factories\Catalog\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Translatable(['name', 'description'])]
class Product extends Model
{
    use HasFactory, HasTranslations;
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
        return $this->belongsToMany(ProductCollection::class, 'product_collection_product')->select('product_collections.*');
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

    public function publish()
    {
        $this->product_status = PublishStatus::Published;
        $this->save();
    }
    public function archive()
    {
        $this->product_status = PublishStatus::Archived;
        $this->save();
    }
    public function unarchive()
    {
        $this->product_status = PublishStatus::Published;
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
