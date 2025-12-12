<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkAttendanceRequest extends FormRequest
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
            'schedule_id' => [
                'required',
                'integer',
                'exists:schedules,id',
            ],
            'attendance_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'attendances' => [
                'required',
                'array',
                'min:1',
            ],
            'attendances.*.student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],
            'attendances.*.status' => [
                'required',
                'string',
                Rule::in(AttendanceStatus::values()),
            ],
            'attendances.*.notes' => [
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
            'schedule_id.required' => 'Schedule is required.',
            'schedule_id.exists' => 'Selected schedule does not exist.',
            'attendance_date.required' => 'Attendance date is required.',
            'attendance_date.before_or_equal' => 'Attendance date cannot be in the future.',
            'attendances.required' => 'At least one attendance record is required.',
            'attendances.min' => 'At least one attendance record is required.',
            'attendances.*.student_id.required' => 'Student is required for each attendance.',
            'attendances.*.student_id.exists' => 'One or more selected students do not exist.',
            'attendances.*.status.required' => 'Status is required for each attendance.',
            'attendances.*.status.in' => 'Invalid status selected for one or more students.',
            'attendances.*.notes.max' => 'Notes must not exceed 500 characters.',
        ];
    }
}
