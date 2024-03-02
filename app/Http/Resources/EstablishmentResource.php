<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="EstablishmentResource",
 *     title="Establishment Resource",
 *     description="Establishment resource schema",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="establishment_name", type="string", example="Hospital XYZ"),
 *     @OA\Property(property="establishment_type", type="string", example="Hospital"),
 *     @OA\Property(property="website_url", type="string", format="url", example="http://hospitalxyz.com"),
 *     @OA\Property(property="address", type="string", example="123 Main St, City, Country")
 * )
 */
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
