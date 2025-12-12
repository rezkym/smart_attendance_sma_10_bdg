<?php

declare(strict_types=1);

namespace App\Http\Requests\Student;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

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
            'full_name' => [
                'required',
                'string',
                'max:100',
            ],
            'gender' => [
                'required',
                new Enum(Gender::class),
            ],
            'birth_place' => [
                'nullable',
                'string',
                'max:100',
            ],
            'birth_date' => [
                'nullable',
                'date',
                'before:today',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'phone_number' => [
                'nullable',
                'string',
                'max:20',
            ],
            'rfid_card_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'rfid_card_number'),
            ],
            'photo' => [
                'nullable',
                'image',
                'max:2048',
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
            'nisn.unique' => 'This NISN is already registered.',
            'nisn.max' => 'NISN must not exceed 20 characters.',
            'nis.required' => 'NIS is required.',
            'nis.unique' => 'This NIS is already registered.',
            'nis.max' => 'NIS must not exceed 20 characters.',
            'full_name.required' => 'Full name is required.',
            'full_name.max' => 'Full name must not exceed 100 characters.',
            'gender.required' => 'Gender is required.',
            'gender.Illuminate\Validation\Rules\Enum' => 'Gender must be L (Male) or P (Female).',
            'birth_place.max' => 'Birth place must not exceed 100 characters.',
            'birth_date.before' => 'Birth date must be before today.',
            'phone_number.max' => 'Phone number must not exceed 20 characters.',
            'rfid_card_number.unique' => 'This RFID card number is already registered.',
            'rfid_card_number.max' => 'RFID card number must not exceed 50 characters.',
            'photo.image' => 'Photo must be an image file.',
            'photo.max' => 'Photo size must not exceed 2MB.',
            'classroom_id.exists' => 'Selected classroom does not exist.',
        ];
    }
}
