<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
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
            'professional_license' => $this->professional_license,
            'education' => $this->education,
            'consultation_cost' => $this->consultation_cost,
            'personal_information' => new UserResource($this->whenLoaded('user')),
            'specialties' => SpecialtyResource::collection($this->specialties),
            'establishments' => EstablishmentResource::collection($this->establishments),
        ];
    }
}
