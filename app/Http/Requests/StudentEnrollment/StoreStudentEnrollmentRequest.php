<?php

declare(strict_types=1);

namespace App\Http\Requests\StudentEnrollment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentEnrollmentRequest extends FormRequest
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
                Rule::exists('students', 'id'),
            ],
            'classroom_id' => [
                'required',
                'integer',
                Rule::exists('classrooms', 'id'),
            ],
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id'),
            ],
            'enrolled_at' => [
                'required',
                'date',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Siswa wajib dipilih.',
            'student_id.exists' => 'Siswa tidak valid.',
            'classroom_id.required' => 'Kelas wajib dipilih.',
            'classroom_id.exists' => 'Kelas tidak valid.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak valid.',
            'enrolled_at.required' => 'Tanggal pendaftaran wajib diisi.',
            'enrolled_at.date' => 'Tanggal pendaftaran tidak valid.',
        ];
    }
}
