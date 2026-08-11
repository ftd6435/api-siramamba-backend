<?php

namespace App\Modules\Content\Models;

use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use CloudflareUpload;

    public const TYPES = ['text', 'json', 'boolean', 'image'];

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public function valueForApi(): mixed
    {
        return match ($this->type) {
            'json' => json_decode($this->value, false, 512, JSON_THROW_ON_ERROR),
            'boolean' => $this->value === '1',
            'image' => $this->getImageUrl($this->value, 'settings'),
            default => $this->value,
        };
    }
}
