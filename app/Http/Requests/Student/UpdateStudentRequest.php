<?php

declare(strict_types=1);

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
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
        $studentId = $this->route('student');

        return [
            // User cannot be changed - profile data comes from linked user
            'nisn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'nisn')->ignore($studentId),
            ],
            'nis' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'nis')->ignore($studentId),
            ],
            'rfid_card_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'rfid_card_number')->ignore($studentId),
            ],
            'enrollment_date' => [
                'nullable',
                'date',
            ],
            'classroom_id' => [
                'nullable',
                'integer',
                'exists:classrooms,id',
            ],
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
            'nisn.required' => 'NISN is required.',
            'nisn.unique' => 'This NISN is already registered by another student.',
            'nisn.max' => 'NISN must not exceed 20 characters.',
            'nis.required' => 'NIS is required.',
            'nis.unique' => 'This NIS is already registered by another student.',
            'nis.max' => 'NIS must not exceed 20 characters.',
            'rfid_card_number.unique' => 'This RFID card number is already registered by another student.',
            'rfid_card_number.max' => 'RFID card number must not exceed 50 characters.',
            'classroom_id.exists' => 'Selected classroom does not exist.',
        ];
    }
}

