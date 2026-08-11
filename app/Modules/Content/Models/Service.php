<?php

namespace App\Modules\Content\Models;

use App\Modules\Administration\Models\User;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use CloudflareUpload;

    protected $fillable = [
        'title',
        'short_description',
        'description',
        'sort_order',
        'thumbnail',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail
            ? $this->getImageUrl($this->thumbnail, 'services/thumbnails')
            : null;
    }
}
