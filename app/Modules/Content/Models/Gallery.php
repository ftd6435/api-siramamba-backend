<?php

namespace App\Modules\Content\Models;

use App\Modules\Administration\Models\User;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gallery extends Model
{
    use CloudflareUpload;

    protected $fillable = [
        'category_id',
        'short_description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryGallery::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? $this->getImageUrl($this->image_path, 'galleries')
            : null;
    }
}
