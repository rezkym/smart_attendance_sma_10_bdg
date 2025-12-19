<?php

declare(strict_types=1);

namespace App\Http\Requests\StudentEnrollment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferStudentRequest extends FormRequest
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
            'transfer_date' => [
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
            'classroom_id.required' => 'Kelas tujuan wajib dipilih.',
            'classroom_id.exists' => 'Kelas tujuan tidak valid.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak valid.',
            'transfer_date.required' => 'Tanggal pindah wajib diisi.',
            'transfer_date.date' => 'Tanggal pindah tidak valid.',
        ];
    }
}
