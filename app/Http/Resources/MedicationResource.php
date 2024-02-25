<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="MedicationResource",
 *     type="object",
 *     title="Medication Resource",
 *     @OA\Property(property="medication_name", type="string", example="Nombre de la medicación"),
 *     @OA\Property(property="description", type="string", example="Descripción de la medicación")
 * )
 */

class MedicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'medication_name' => $this->medication_name,
            'description' => $this->description
        ];
    }
}
