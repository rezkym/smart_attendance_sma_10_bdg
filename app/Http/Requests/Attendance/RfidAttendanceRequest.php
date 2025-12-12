<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class RfidAttendanceRequest extends FormRequest
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
            'rfid_card_number' => [
                'required',
                'string',
                'max:50',
            ],
            'schedule_id' => [
                'required',
                'integer',
                'exists:schedules,id',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rfid_card_number.required' => 'RFID card number is required.',
            'rfid_card_number.max' => 'RFID card number is too long.',
            'schedule_id.required' => 'Schedule is required.',
            'schedule_id.exists' => 'Selected schedule does not exist.',
        ];
    }
}
