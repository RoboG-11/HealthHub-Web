<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id(); // Obtener el ID del usuario autenticado

        $rules = [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:20',
            'sex' => 'required|in:male,female,other',
            'age' => 'required|integer|min:0',
            'date_of_birth' => 'required|date',
            'link_photo' => 'nullable|string',
        ];

        // Aplicar reglas solo si es una solicitud de actualización (PUT o PATCH)
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // Obtener los campos enviados en la solicitud
            $sentFields = array_keys($this->all());
            // Aplicar las reglas solo a los campos enviados
            $rules = array_intersect_key($rules, array_flip($sentFields));
        }

        return $rules;
    }
}
