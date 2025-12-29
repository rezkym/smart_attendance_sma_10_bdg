<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class GetSchedulesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('teacher-schedules.view-own') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'day_of_week' => ['nullable', 'integer', 'min:1', 'max:7'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
        ];
    }

    /**
     * Get validated day of week filter.
     */
    public function getDayOfWeek(): ?int
    {
        return $this->validated('day_of_week') ? (int) $this->validated('day_of_week') : null;
    }

    /**
     * Get validated semester ID filter.
     */
    public function getSemesterId(): ?int
    {
        return $this->validated('semester_id') ? (int) $this->validated('semester_id') : null;
    }

    /**
     * Get validated academic year ID filter.
     */
    public function getAcademicYearId(): ?int
    {
        return $this->validated('academic_year_id') ? (int) $this->validated('academic_year_id') : null;
    }

    /**
     * Get all filters as array.
     *
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        $filters = [];

        if ($this->getDayOfWeek() !== null) {
            $filters['day_of_week'] = $this->getDayOfWeek();
        }

        if ($this->getSemesterId() !== null) {
            $filters['semester_id'] = $this->getSemesterId();
        }

        if ($this->getAcademicYearId() !== null) {
            $filters['academic_year_id'] = $this->getAcademicYearId();
        }

        return $filters;
    }
}
