<?php

namespace App\Modules\Content\Resources;

use App\Modules\Administration\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class GalleryResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new GalleryCategoryResource($this->whenLoaded('category')),
            'image_url' => $this->image_url,
            'short_description' => $this->short_description,
            'is_active' => $this->is_active,
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
