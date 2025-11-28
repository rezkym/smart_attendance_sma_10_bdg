<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignStudentClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('students.assign-classroom') ?? false;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
        ];
    }
}
