<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignTeacherSubjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('teachers.assign-subjects') ?? false;
    }

    public function rules(): array
    {
        return [
            'subject_ids' => ['required', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id', 'distinct'],
        ];
    }
}
