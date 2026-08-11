<?php

namespace App\Modules\Content\Resources;

use App\Modules\Administration\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class GalleryCategoryResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_by' => $this->whenLoaded(
                'creator',
                fn () => new UserResource($this->creator)
            ),
            'updated_by' => $this->whenLoaded(
                'updater',
                fn () => new UserResource($this->updater)
            ),
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i:s'),
        ];
    }
}
