<?php

namespace App\Modules\Evenement\Models;

use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_id', 'image_path'])]
class EventImage extends Model
{
    use CloudflareUpload;

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return $this->getImageUrl($this->image_path, 'events');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
