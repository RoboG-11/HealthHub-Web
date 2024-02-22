<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
