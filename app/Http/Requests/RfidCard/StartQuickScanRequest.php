<?php

declare(strict_types=1);

namespace App\Http\Requests\RfidCard;

use Illuminate\Foundation\Http\FormRequest;

class StartQuickScanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('rfid-cards.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'device_id' => ['required', 'integer', 'exists:iot_devices,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'expiry_years' => ['nullable', 'integer', 'in:0,1,2,3'],
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
            'device_id.required' => 'Device harus dipilih.',
            'device_id.exists' => 'Device tidak ditemukan.',
            'user_id.required' => 'User harus dipilih.',
            'user_id.exists' => 'User tidak ditemukan.',
            'expiry_years.in' => 'Pilihan expiry tidak valid.',
        ];
    }

    /**
     * Get validated device ID as integer.
     */
    public function getDeviceId(): int
    {
        return (int) $this->validated('device_id');
    }

    /**
     * Get validated user ID as integer.
     */
    public function getUserId(): int
    {
        return (int) $this->validated('user_id');
    }

    /**
     * Get validated expiry years as integer.
     */
    public function getExpiryYears(): int
    {
        return (int) ($this->validated('expiry_years') ?? 0);
    }
}
