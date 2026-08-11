<?php

namespace App\Modules\Content\Models;

use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blog extends Model
{
    use CloudflareUpload;

    protected $fillable = [
        'category_id',
        'title',
        'short_description',
        'description',
        'thumbnail',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(BlogImage::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail
            ? $this->getImageUrl($this->thumbnail, 'blogs/thumbnails')
            : null;
    }
}
