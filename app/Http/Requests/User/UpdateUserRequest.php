<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'full_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'gender' => [
                'nullable',
                'string',
                Rule::enum(Gender::class),
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [
                'nullable',
                'string',
                Password::min(8),
                'confirmed',
            ],
            'roles' => [
                'nullable',
                'array',
            ],
            'roles.*' => [
                'string',
                'exists:roles,name',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'full_name.max' => 'Full name must not exceed 100 characters.',
            'gender.enum' => 'Invalid gender value.',
            'birth_place.max' => 'Birth place must not exceed 100 characters.',
            'birth_date.before' => 'Birth date must be before today.',
            'phone_number.max' => 'Phone number must not exceed 20 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'roles.*.exists' => 'One or more selected roles are invalid.',
        ];
    }
}

