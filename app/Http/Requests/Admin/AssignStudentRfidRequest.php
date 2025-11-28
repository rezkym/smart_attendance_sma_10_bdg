<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignStudentRfidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('students.assign-rfid') ?? false;
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            'rfid_card_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('students', 'rfid_card_number')->ignore($studentId),
            ],
        ];
    }
}
