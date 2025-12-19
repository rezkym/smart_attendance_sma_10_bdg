<?php

declare(strict_types=1);

namespace App\Http\Requests\Semester;

use App\Enums\SemesterType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSemesterRequest extends FormRequest
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
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id'),
            ],
            'type' => [
                'required',
                'integer',
                Rule::in(SemesterType::values()),
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
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
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak valid.',
            'type.required' => 'Tipe semester wajib dipilih.',
            'type.in' => 'Tipe semester tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal akhir wajib diisi.',
            'end_date.after' => 'Tanggal akhir harus setelah tanggal mulai.',
        ];
    }
}
