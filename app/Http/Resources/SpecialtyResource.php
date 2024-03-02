<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SpecialtyResource",
 *     title="Specialty Resource",
 *     description="Specialty resource schema",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="specialty_name", type="string", example="Cardiology"),
 *     @OA\Property(property="description", type="string", example="Study of the heart and its functions")
 * )
 */
class SpecialtyResource extends JsonResource
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
            'specialty_name' => $this->specialty_name,
            'description' => $this->description
        ];
    }
}
