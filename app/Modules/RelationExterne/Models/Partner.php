<?php

namespace App\Modules\RelationExterne\Models;

use App\Modules\Administration\Models\User;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'type_partner_id',
    'company',
    'short_description',
    'logo',
    'website_link',
    'is_active',
    'created_by',
    'updated_by',
])]
class Partner extends Model
{
    use CloudflareUpload;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected $appends = [
        'logo_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        return $this->getImageUrl($this->logo, 'partners');
    }

    public function typePartner(): BelongsTo
    {
        return $this->belongsTo(TypePartner::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
