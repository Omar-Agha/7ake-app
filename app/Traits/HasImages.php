<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasImages
{
    /**
     * Boot the HasImages trait for a model.
     */
    public static function bootHasImages(): void
    {
        static::deleting(function ($model) {
            // Optionally delete images when the model is deleted
            if (method_exists($model, 'images')) {
                foreach ($model->images as $image) {
                    $image->delete();
                }
            }
        });
    }

    /**
     * Get all of the model's images.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images(): MorphMany
    {
        return $this->morphMany(\App\Models\Image::class, 'imageable');
    }
   
    public function getImageByTag(string $tag): ?\App\Models\Image
    {
        return $this->images()->where('tag', $tag)->first();
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images()->where('is_primary', true)->first() 
            ?? $this->images()->first();
    }

    /**
     * Helper to attach a new image path.
     */
    public function addImage(string $path, bool $isPrimary = false): Model
    {
        if ($isPrimary) {
            // Unset any existing primary images
            $this->images()->update(['is_primary' => false]);
        }

        return $this->images()->create([
            'path' => $path,
            'is_primary' => $isPrimary || $this->images()->count() === 0,
        ]);
    }
}