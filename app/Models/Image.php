<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'path',
        'alt_text',
        'tag'
    ];
    /**
     * Get the full URL for the image.
     *
     * @return string|null
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        return asset('storage/' . ltrim($this->path, '/'));
    }

    /**
     * Get the parent imageable model (product, variant, etc).
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
