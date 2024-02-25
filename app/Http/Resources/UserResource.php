<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     title="User Resource",
 *     description="User resource schema",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="rol", type="string", example="admin"),
 *     @OA\Property(property="name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="phone", type="string", example="123456789"),
 *     @OA\Property(property="sex", type="string", example="male"),
 *     @OA\Property(property="age", type="integer", example="30"),
 *     @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01"),
 * )
 */
class UserResource extends JsonResource
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
            'rol' => $this->rol,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'sex' => $this->sex,
            'age' => $this->age,
            'date_of_birth' => $this->date_of_birth
        ];
    }
}
