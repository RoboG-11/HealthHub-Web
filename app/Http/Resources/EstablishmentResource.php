<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstablishmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'establishment_name' => $this->establishment_name,
            'establishment_type' => $this->establishment_type,
            'website_url' => $this->website_url,
            'address' => $this->address
        ];
    }
}
