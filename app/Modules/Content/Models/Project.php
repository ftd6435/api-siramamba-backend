<?php

namespace App\Modules\Content\Models;

use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use CloudflareUpload;

    protected $fillable = [
        'category_id',
        'title',
        'short_description',
        'description',
        'status',
        'is_featured',
        'country',
        'city',
        'address',
        'start_date',
        'end_date',
        'progess_percentage',
        'list_details',
        'is_active',
        'thumbnail',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'list_details' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail
            ? $this->getImageUrl($this->thumbnail, 'projects/thumbnails')
            : null;
    }
}
