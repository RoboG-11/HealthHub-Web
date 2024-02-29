<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        ];
    }
}
