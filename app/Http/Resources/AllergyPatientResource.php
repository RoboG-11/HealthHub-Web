<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AllergyPatientResource",
 *     title="AllergyPatient Resource",
 *     description="AllergyPatient resource schema",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="patient_user_id", type="integer", example="1"),
 *     @OA\Property(property="allergy_id", type="integer", example="1"),
 * )
 */
class AllergyPatientResource extends JsonResource
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
            'allergy_id' => $this->allergy_id
        ];
    }
}
