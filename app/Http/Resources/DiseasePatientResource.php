<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DiseasePatientResource",
 *     title="Disease Patient Resource",
 *     description="Disease Patient resource schema",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="patient_user_id", type="integer", example="1"),
 *     @OA\Property(property="disease_id", type="integer", example="1")
 * )
 */
class DiseasePatientResource extends JsonResource
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
            'disease_id' => $this->disease_id
        ];
    }
}
