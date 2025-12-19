<?php

declare(strict_types=1);

namespace App\Http\Requests\StudentEnrollment;

use App\Enums\EnrollmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentEnrollmentRequest extends FormRequest
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
                Rule::exists('classrooms', 'id'),
            ],
            'enrolled_at' => [
                'sometimes',
                'required',
                'date',
            ],
            'left_at' => [
                'nullable',
                'date',
                'after_or_equal:enrolled_at',
            ],
            'status' => [
                'sometimes',
                'required',
                'integer',
                Rule::in(EnrollmentStatus::values()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'classroom_id.exists' => 'Kelas tidak valid.',
            'enrolled_at.date' => 'Tanggal pendaftaran tidak valid.',
            'left_at.after_or_equal' => 'Tanggal keluar harus setelah atau sama dengan tanggal pendaftaran.',
            'status.in' => 'Status enrollment tidak valid.',
        ];
    }
}
