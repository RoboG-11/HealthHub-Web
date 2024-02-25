<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DiseaseResource",
 *     title="Disease Resource",
 *     description="Disease resource schema",
 *     @OA\Property(property="disease_name", type="string", example="Influenza"),
 *     @OA\Property(property="description", type="string", example="Viral infection that affects the respiratory system.")
 * )
 */
class DiseaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'disease_name' => $this->disease_name,
            'description' => $this->description
        ];
    }
}
