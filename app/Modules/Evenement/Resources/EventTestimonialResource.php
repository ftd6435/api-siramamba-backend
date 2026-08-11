<?php

namespace App\Modules\Evenement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class EventTestimonialResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'message' => $this->message,
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
        ];
    }
}
