<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'street' => $this->street,
            'interior_number' => $this->interior_number,
            'exterior_number' => $this->exterior_number,
            'neighborhood' => $this->neighborhood,
            'zip_code' => $this->zip_code,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }
}
