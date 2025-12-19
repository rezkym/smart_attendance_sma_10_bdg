<?php

declare(strict_types=1);

namespace App\Http\Requests\RfidCard;

use Illuminate\Foundation\Http\FormRequest;

class CheckQuickScanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('rfid-cards.view') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'session_id.required' => 'Session ID diperlukan.',
        ];
    }

    /**
     * Get validated session ID.
     */
    public function getSessionId(): string
    {
        return (string) $this->validated('session_id');
    }
}
