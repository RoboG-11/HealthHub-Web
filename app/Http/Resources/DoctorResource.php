<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso para representar un doctor.
 *
 * @OA\Schema(
 *     schema="DoctorResource",
 *     title="Doctor Resource",
 *     description="Doctor resource schema",
 *     @OA\Property(property="professional_license", type="string", example="12345"),
 *     @OA\Property(property="education", type="string", example="Medical School"),
 *     @OA\Property(property="consultation_cost", type="integer", example="50"),
 *     @OA\Property(property="personal_information", ref="#/components/schemas/UserResource"),
 *     @OA\Property(property="specialties", type="array", @OA\Items(ref="#/components/schemas/SpecialtyResource")),
 *     @OA\Property(property="establishments", type="array", @OA\Items(ref="#/components/schemas/EstablishmentResource")),
 *     @OA\Property(property="schedules", type="array", @OA\Items(type="string"))
 * )
 */
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
            'schedules' => $this->schedules
        ];
    }
}
