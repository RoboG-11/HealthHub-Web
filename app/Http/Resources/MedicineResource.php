<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="MedicineResource",
 *     title="MedicineResource",
 *     description="Recursos de un medicamento.",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="summary_id", type="integer", example="1"),
 *     @OA\Property(property="medicine_name", type="string", example="Paracetamol"),
 *     @OA\Property(property="dosage", type="string", example="500 mg"),
 *     @OA\Property(property="frequency", type="string", example="Twice daily"),
 *     @OA\Property(property="duration", type="string", example="7 days"),
 *     @OA\Property(property="notes", type="string", example="Take with food.")
 * )
 */
class MedicineResource extends JsonResource
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
            'summary_id' => $this->summary_id,
            'medicine_name' => $this->medicine_name,
            'dosage' => $this->dosage,
            'frequency' => $this->frequency,
            'duration' => $this->duration,
            'notes' => $this->notes
        ];
    }
}
