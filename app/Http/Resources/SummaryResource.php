<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SummaryResource",
 *     title="Summary Resource",
 *     description="Summary resource schema",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="appointment_id", type="integer", example="1"),
 *     @OA\Property(property="diagnosis", type="string", example="Diagnosis details"),
 *     @OA\Property(property="medicines", type="array", @OA\Items(
 *         type="object",
 *         @OA\Property(property="id", type="integer", example="1"),
 *         @OA\Property(property="name", type="string", example="Medicine Name"),
 *         @OA\Property(property="dosage", type="string", example="Dosage details"),
 *         @OA\Property(property="frequency", type="string", example="Frequency details"),
 *         @OA\Property(property="duration", type="string", example="Duration details"),
 *         @OA\Property(property="notes", type="string", example="Additional notes")
 *     ))
 * )
 */
class SummaryResource extends JsonResource
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
            'appointment_id' => $this->appointment_id,
            'diagnosis' => $this->diagnosis,
            'medicines' => $this->medicines
        ];
    }
}
