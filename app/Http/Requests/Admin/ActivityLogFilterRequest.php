<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ActivityLogFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('activity-logs.view') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'log_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'event' => ['sometimes', 'nullable', 'string', 'in:created,updated,deleted'],
            'causer_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'subject_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'subject_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'date_from' => ['sometimes', 'nullable', 'date'],
            'date_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    /**
     * Get validated filters as typed array
     *
     * @return array{
     *     log_name?: string,
     *     event?: string,
     *     causer_id?: int,
     *     subject_type?: string,
     *     subject_id?: int,
     *     date_from?: string,
     *     date_to?: string
     * }
     */
    public function getFilters(): array
    {
        return array_filter([
            'log_name' => $this->validated('log_name'),
            'event' => $this->validated('event'),
            'causer_id' => $this->validated('causer_id') ? (int) $this->validated('causer_id') : null,
            'subject_type' => $this->validated('subject_type'),
            'subject_id' => $this->validated('subject_id') ? (int) $this->validated('subject_id') : null,
            'date_from' => $this->validated('date_from'),
            'date_to' => $this->validated('date_to'),
        ], fn ($value) => $value !== null);
    }
}
