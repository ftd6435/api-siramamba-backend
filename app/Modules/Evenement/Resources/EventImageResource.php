<?php

namespace App\Modules\Evenement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class EventImageResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
        ];
    }
}
