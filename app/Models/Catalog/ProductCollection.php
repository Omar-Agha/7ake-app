<?php

namespace App\Models\Catalog;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Model;

class ProductCollection extends Model
{
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => \App\Enums\PublishStatus::class,
    ];

    /**
     * The products that belong to this product collection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
    public function publish() {
        $this->status = PublishStatus::Published;
    }

    public function archive() {
        $this->status = PublishStatus::Archived;
    }
    public function unarchive() {
        $this->status = PublishStatus::Published;
    }
}
