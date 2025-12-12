<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
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
            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],
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
            'check_in_time' => [
                'nullable',
                'date_format:H:i:s',
            ],
            'check_out_time' => [
                'nullable',
                'date_format:H:i:s',
                'after:check_in_time',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(AttendanceStatus::values()),
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
            'student_id.required' => 'Student is required.',
            'student_id.exists' => 'Selected student does not exist.',
            'schedule_id.required' => 'Schedule is required.',
            'schedule_id.exists' => 'Selected schedule does not exist.',
            'attendance_date.required' => 'Attendance date is required.',
            'attendance_date.before_or_equal' => 'Attendance date cannot be in the future.',
            'check_in_time.date_format' => 'Check-in time must be in HH:MM:SS format.',
            'check_out_time.date_format' => 'Check-out time must be in HH:MM:SS format.',
            'check_out_time.after' => 'Check-out time must be after check-in time.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'notes.max' => 'Notes must not exceed 500 characters.',
        ];
    }
}
