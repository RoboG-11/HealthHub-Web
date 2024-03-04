<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DoctorSpecialtyResource",
 *     title="DoctorSpecialtyResource",
 *     description="Recursos de la relación entre un doctor y una especialidad médica.",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="doctor_user_id", type="integer", example="1"),
 *     @OA\Property(property="specialty_id", type="integer", example="1"),
 * )
 */
class DoctorSpecialtyResource extends JsonResource
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
            // 'specialty_id' => $this->specialty_id,
            'specialty' => $this->specialty,
        ];
    }
}
