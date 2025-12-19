<?php

declare(strict_types=1);

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled via middleware/policy
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('students', 'user_id'),
            ],
            'nisn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'nisn'),
            ],
            'nis' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'nis'),
            ],
            'rfid_card_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'rfid_card_number'),
            ],
            'enrollment_date' => [
                'nullable',
                'date',
            ],
            // classroom_id removed - Phase G: use enrollment
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a user.',
            'user_id.exists' => 'Selected user does not exist.',
            'user_id.unique' => 'This user already has a student profile.',
            'nisn.required' => 'NISN is required.',
            'nisn.unique' => 'This NISN is already registered.',
            'nisn.max' => 'NISN must not exceed 20 characters.',
            'nis.required' => 'NIS is required.',
            'nis.unique' => 'This NIS is already registered.',
            'nis.max' => 'NIS must not exceed 20 characters.',
            'rfid_card_number.unique' => 'This RFID card number is already registered.',
            'rfid_card_number.max' => 'RFID card number must not exceed 50 characters.',
            // classroom_id message removed - Phase G
        ];
    }
}

