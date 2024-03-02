<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AppointmentResource",
 *     title="Appointment Resource",
 *     description="Appointment resource schema",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="user_id", type="integer", example="1"),
 *     @OA\Property(property="patient_id", type="integer", example="1"),
 *     @OA\Property(property="date", type="string", format="date-time", example="2024-03-02 10:00:00"),
 *     @OA\Property(property="notes", type="string", example="Checkup"),
 * )
 */
class AppointmentResource extends JsonResource
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
            'doctor' => $this->doctor,
            'patient' => $this->patient,
            'appointment_datetime' => $this->appointment_datetime,
            'link' => $this->link,
            'status' => $this->status,
            'reason' => $this->reason,
            'consultation_cost' => $this->consultation_cost,
            // 'summary' => $this->summary
            'summary' => new SummaryResource($this->summary),
        ];
    }
}
