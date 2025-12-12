<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
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
            'check_in_time' => [
                'nullable',
                'date_format:H:i:s',
            ],
            'check_out_time' => [
                'nullable',
                'date_format:H:i:s',
            ],
            'status' => [
                'sometimes',
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
            'check_in_time.date_format' => 'Check-in time must be in HH:MM:SS format.',
            'check_out_time.date_format' => 'Check-out time must be in HH:MM:SS format.',
            'status.in' => 'Invalid status selected.',
            'notes.max' => 'Notes must not exceed 500 characters.',
        ];
    }
}
