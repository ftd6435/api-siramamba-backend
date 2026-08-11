<?php

namespace App\Modules\RelationExterne\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class PartnerResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'type_partner' => new TypePartnerResource($this->whenLoaded('typePartner')),
            'company' => $this->company,
            'short_description' => $this->short_description,
            'logo_url' => $this->logo_url,
            'website_link' => $this->website_link,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i:s'),
        ];
    }
}
