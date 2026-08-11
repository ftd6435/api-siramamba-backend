<?php

namespace App\Modules\Content\Models;

use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogImage extends Model
{
    use CloudflareUpload;

    protected $fillable = [
        'blog_id',
        'image_path',
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    public function getUrlAttribute(): ?string
    {
        return $this->image_path
            ? $this->getImageUrl($this->image_path, 'blogs/images')
            : null;
    }
}
