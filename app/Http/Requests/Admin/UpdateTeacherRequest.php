<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('teachers.update') ?? false;
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher')?->id;

        return [
            'teacher_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('teachers', 'teacher_number')->ignore($teacherId),
            ],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'address' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
        ];
    }
}
