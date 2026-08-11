<?php

namespace App\Modules\Evenement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class ParticipantResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'event' => new EventResource($this->whenLoaded('event')),
            'name' => $this->name,
            'telephone' => $this->telephone,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i:s'),
        ];
    }
}
