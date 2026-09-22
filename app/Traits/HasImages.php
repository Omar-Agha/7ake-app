<?php

namespace App\Traits;

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
    /**
     * Get an image by its tag.
     *
     * @param string $tag
     * @return \App\Models\Image|null
     */
    public function getImageByTag(string $tag): ?\App\Models\Image
    {
        return $this->images()->where('tag', $tag)->first();
    }
}