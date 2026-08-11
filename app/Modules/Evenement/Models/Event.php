<?php

namespace App\Modules\Evenement\Models;

use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
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
    'list_details',
    'is_active',
    'thumbnail',
    'video_url_link',
])]
class Event extends Model
{
    use CloudflareUpload;

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

    protected $appends = [
        'thumbnail_url',
    ];

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }

        return $this->getImageUrl($this->thumbnail, 'events');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }
}
