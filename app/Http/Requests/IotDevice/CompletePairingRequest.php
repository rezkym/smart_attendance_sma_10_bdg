<?php

declare(strict_types=1);

namespace App\Http\Requests\IotDevice;

use Illuminate\Foundation\Http\FormRequest;

class CompletePairingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'device_code' => ['required', 'string', 'max:50'],
            'ip_address' => ['required', 'ip'],
            'api_key' => ['required', 'string', 'size:64'],
            'name' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'firmware_version' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_code.required' => 'Device code is required.',
            'ip_address.required' => 'IP address is required.',
            'ip_address.ip' => 'IP address must be a valid IP address.',
        ];
    }
}
