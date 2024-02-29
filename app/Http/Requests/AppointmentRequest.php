<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

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
        return [
            'doctor_id' => 'required|exists:doctors,user_id',
            'patient_id' => 'required|exists:patients,user_id',
            'appointment_datetime' => 'required|date_format:Y-m-d H:i:s',
            'link' => 'nullable|url',
            'status' => 'required|in:scheduled,cancelled,completed',
            'reason' => 'required|string',
            'consultation_cost' => 'required|numeric',
        ];
    }
}
