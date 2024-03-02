<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ScheduleResource",
 *     title="ScheduleResource",
 *     description="Recursos de un horario de citas.",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="doctor_id", type="integer", example="1"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2024-03-02T10:00:00"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2024-03-02T11:00:00"),
 *     @OA\Property(property="day_of_week", type="string", example="Monday"),
 * )
 */
class ScheduleResource extends JsonResource
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
            'doctor_id' => $this->doctor_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'day_of_week' => $this->day_of_week,
        ];
    }
}
