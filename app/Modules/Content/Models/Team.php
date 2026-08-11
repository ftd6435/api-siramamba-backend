<?php

namespace App\Modules\Content\Models;

use App\Modules\Administration\Models\User;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends Model
{
    use CloudflareUpload;

    protected $fillable = [
        'name',
        'post',
        'short_description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar
            ? $this->getImageUrl($this->avatar, 'teams/avatars')
            : null;
    }
}
