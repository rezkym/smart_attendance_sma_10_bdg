<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('students.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'exists:users,id',
                'unique:students,user_id',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::find($value);
                    if ($user && ! $user->hasRole('student')) {
                        $fail('User yang dipilih harus memiliki role student.');
                    }
                },
            ],

            'student_number' => ['required', 'string', 'max:50', 'unique:students,student_number'],
            'rfid_card_number' => ['nullable', 'string', 'max:100', 'unique:students,rfid_card_number'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female'])],
            'address' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:50'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
        ];
    }
}
