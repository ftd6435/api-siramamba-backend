<?php

namespace App\Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class ServiceImage extends Model
{
    protected $fillable = [
        'service_id',
        'image_path',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        // CKEditor persists this URL in HTML, so it must use a non-expiring public base URL.
        $publicUrl = config('filesystems.disks.r2.public_url');

        if (! is_string($publicUrl) || trim($publicUrl) === '') {
            throw new RuntimeException('R2_PUBLIC_URL doit être configurée pour exposer les images de services.');
        }

        return rtrim($publicUrl, '/').'/images/services/images/'.$this->image_path;
    }
}
