<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
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
        $teacherId = $this->route('teacher');

        return [
            // Only teacher fields - user data is not editable here
            'nip' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('teachers', 'nip')->ignore($teacherId),
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nip.unique' => 'This NIP is already registered.',
            'nip.max' => 'NIP must not exceed 30 characters.',
        ];
    }
}

