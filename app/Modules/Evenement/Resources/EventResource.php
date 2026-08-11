<?php

namespace App\Modules\Evenement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class EventResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'category' => new EventCategoryResource($this->whenLoaded('category')),
            'title' => $this->title,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'start_date' => $this->start_date?->format('d-m-Y'),
            'end_date' => $this->end_date?->format('d-m-Y'),
            'list_details' => $this->list_details,
            'is_active' => $this->is_active,
            'thumbnail_url' => $this->thumbnail_url,
            'video_url_link' => $this->video_url_link,
            'images' => EventImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i:s'),
        ];
    }
}
