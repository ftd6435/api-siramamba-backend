<?php

namespace App\Modules\Content\Models;

use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectImage extends Model
{
    use CloudflareUpload;

    protected $fillable = [
        'project_id',
        'image_path',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getUrlAttribute(): ?string
    {
        return $this->image_path
            ? $this->getImageUrl($this->image_path, 'projects/gallery')
            : null;
    }
}
