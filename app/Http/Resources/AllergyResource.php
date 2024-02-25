<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AllergyResource",
 *     title="Allergy Resource",
 *     description="Allergy resource schema",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="allergy_name", type="string", example="Peanuts"),
 *     @OA\Property(property="description", type="string", example="Allergy to peanuts"),
 * )
 */
class AllergyResource extends JsonResource
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
            'allergy_name' => $this->allergy_name,
            'description' => $this->description
        ];
    }
}
