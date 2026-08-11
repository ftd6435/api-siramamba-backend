<?php

namespace App\Modules\Content\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class ProjectResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'title' => $this->title,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'progess_percentage' => $this->progess_percentage,
            'list_details' => $this->list_details,
            'is_active' => $this->is_active,
            'thumbnail_url' => $this->thumbnail_url,
            'images' => ProjectImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i:s'),
        ];
    }
}
