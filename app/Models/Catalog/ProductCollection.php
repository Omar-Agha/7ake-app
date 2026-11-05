<?php

namespace App\Models\Catalog;

use App\Enums\PublishStatus;
use App\Traits\HasImages;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string $name
 * @property string|null $description
 * @property PublishStatus $status
 * @property \Carbon\CarbonImmutable|null $published_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalog\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCollection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCollection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCollection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCollection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCollection whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCollection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCollection whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCollection wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCollection whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCollection whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Translatable('name', 'description')]
class ProductCollection extends Model
{
    use HasImages, HasTranslations;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Add the attributes you want to be mass assignable, for example:
        'name',
        'description',
        'status',
        'published_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => \App\Enums\PublishStatus::class,
        'published_at' => 'datetime',
    ];

    /**
     * The products that belong to this product collection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_collection_product');
    }
    public function immediatePublish(): void
    {

        $this->status = PublishStatus::Published;
        $this->published_at = now();
    }
    public function futurePublish(DateTimeInterface|string $time): void
    {
        $this->published_at = $time;
    }

    public function archive()
    {
        $this->status = PublishStatus::Archived;
    }
    public function unarchive()
    {
        $this->status = PublishStatus::Published;
    }
}
