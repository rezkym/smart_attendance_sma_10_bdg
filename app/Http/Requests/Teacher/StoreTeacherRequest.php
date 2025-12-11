<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
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
                Rule::unique('teachers', 'user_id'),
            ],
            'nip' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('teachers', 'nip'),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'address' => [
                'nullable',
                'string',
                'max:1000',
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
            'user_id.required' => 'Please select a user.',
            'user_id.exists' => 'Selected user does not exist.',
            'user_id.unique' => 'This user already has a teacher profile.',
            'nip.unique' => 'This NIP is already registered.',
            'nip.max' => 'NIP must not exceed 30 characters.',
            'phone.max' => 'Phone number must not exceed 20 characters.',
        ];
    }
}
