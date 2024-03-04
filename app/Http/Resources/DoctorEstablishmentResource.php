<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DoctorEstablishmentResource",
 *     title="DoctorEstablishmentResource",
 *     description="Recursos de la relación entre un doctor y un establecimiento médico.",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="doctor_user_id", type="integer", example="1"),
 *     @OA\Property(property="establishment_id", type="integer", example="1"),
 * )
 */
class DoctorEstablishmentResource extends JsonResource
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
            'doctor_user_id' => $this->doctor_user_id,
            'establishment' => $this->establishment
        ];
    }
}
