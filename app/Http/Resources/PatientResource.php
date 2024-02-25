<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso para representar un paciente en la API.
 *
 * @OA\Schema(
 *     title="PatientResource",
 *     description="Recurso para representar un paciente en la API",
 *     @OA\Property(property="weight", type="string", example="70"),
 *     @OA\Property(property="height", type="string", example="180"),
 *     @OA\Property(property="nss", type="string", example="123456789"),
 *     @OA\Property(property="occupation", type="string", example="Doctor"),
 *     @OA\Property(property="blood_type", type="string", example="AB+"),
 *     @OA\Property(property="emergency_contact_phone", type="string", example="555-123456"),
 *     @OA\Property(property="personal_information", ref="#/components/schemas/UserResource"),
 *     @OA\Property(property="allergies", type="array", @OA\Items(ref="#/components/schemas/AllergyResource")),
 *     @OA\Property(property="diseases", type="array", @OA\Items(ref="#/components/schemas/DiseaseResource")),
 *     @OA\Property(property="medications", type="array", @OA\Items(ref="#/components/schemas/MedicationResource"))
 * )
 */
class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing('user');

        return [
            'weight' => $this->weight,
            'height' => $this->height,
            'nss' => $this->nss,
            'occupation' => $this->occupation,
            'blood_type' => $this->blood_type,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            // 'personal_information' => $this->user,
            'personal_information' => new UserResource($this->whenLoaded('user')),
            // 'personal_information' => UserResource::collection($this->user),
            // 'allergies' => $this->allergies,
            'allergies' => AllergyResource::collection($this->allergies),
            // 'diseases' => $this->diseases,
            'diseases' => DiseaseResource::collection($this->diseases),
            // 'medications' => $this->medications
            'medications' => MedicationResource::collection($this->medications),
        ];
    }
}
