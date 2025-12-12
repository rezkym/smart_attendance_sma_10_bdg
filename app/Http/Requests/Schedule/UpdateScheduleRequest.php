<?php

declare(strict_types=1);

namespace App\Http\Requests\Schedule;

use App\Enums\DayOfWeek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleRequest extends FormRequest
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
            'classroom_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:classrooms,id',
            ],
            'subject_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:subjects,id',
            ],
            'teacher_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:teachers,id',
            ],
            'academic_year_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:academic_years,id',
            ],
            'day_of_week' => [
                'sometimes',
                'required',
                'integer',
                Rule::in(DayOfWeek::values()),
            ],
            'start_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'classroom_id.exists' => 'Selected classroom does not exist.',
            'subject_id.exists' => 'Selected subject does not exist.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'academic_year_id.exists' => 'Selected academic year does not exist.',
            'day_of_week.in' => 'Day of week must be between 1 (Monday) and 6 (Saturday).',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            'end_time.date_format' => 'End time must be in HH:MM format.',
            'end_time.after' => 'End time must be after start time.',
            'notes.max' => 'Notes must not exceed 500 characters.',
        ];
    }
}
