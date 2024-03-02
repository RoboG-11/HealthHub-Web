<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AddressResource",
 *     title="Address Resource",
 *     description="Address resource schema",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="street", type="string", example="123 Main St"),
 *     @OA\Property(property="city", type="string", example="Springfield"),
 *     @OA\Property(property="state", type="string", example="IL"),
 *     @OA\Property(property="zip_code", type="string", example="12345"),
 *     @OA\Property(property="country", type="string", example="USA"),
 * )
 */
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
