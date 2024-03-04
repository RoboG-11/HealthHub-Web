<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PatientUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 400));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules =  [
            'weight' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'nss' => [
                'required',
                'string',
                'max:255',
                Rule::unique('patients', 'nss')->ignore(Auth::user()->patient->user_id, 'user_id')
            ],
            'occupation' => 'nullable|string|max:255',
            'blood_type' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
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
