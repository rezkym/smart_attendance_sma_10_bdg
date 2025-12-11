<?php

declare(strict_types=1);

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassroomRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classrooms')->where(function ($query) {
                    return $query->where('academic_year_id', $this->input('academic_year_id'));
                }),
            ],
            'grade_level' => [
                'required',
                'integer',
                'in:10,11,12',
            ],
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],
            'capacity' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'description' => [
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
            'name.required' => 'Classroom name is required.',
            'name.max' => 'Classroom name must not exceed 50 characters.',
            'name.unique' => 'This classroom name already exists for the selected academic year.',
            'grade_level.required' => 'Grade level is required.',
            'grade_level.in' => 'Grade level must be 10, 11, or 12.',
            'academic_year_id.required' => 'Academic year is required.',
            'academic_year_id.exists' => 'Selected academic year does not exist.',
            'capacity.min' => 'Capacity must be at least 1.',
            'capacity.max' => 'Capacity must not exceed 100.',
        ];
    }
}
