<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso para representar una relación entre Medicación y Paciente.
 *
 * @OA\Schema(
 *     title="MedicationPatientResource",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="patient_user_id", type="integer", example="1"),
 *     @OA\Property(property="medication_id", type="integer", example="1")
 * )
 */
class MedicationPatientResource extends JsonResource
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
            'patient_user_id' => $this->patient_user_id,
            'medication_id' => $this->medication_id
        ];
    }
}
