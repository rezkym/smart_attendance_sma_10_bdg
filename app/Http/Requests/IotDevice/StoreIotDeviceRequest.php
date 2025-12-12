<?php

declare(strict_types=1);

namespace App\Http\Requests\IotDevice;

use App\Enums\IotDeviceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIotDeviceRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'device_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('iot_devices', 'device_code'),
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'classroom_id' => [
                'nullable',
                'integer',
                'exists:classrooms,id',
            ],
            'status' => [
                'nullable',
                Rule::in(IotDeviceStatus::values()),
            ],
            'firmware_version' => [
                'nullable',
                'string',
                'max:20',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Device name is required.',
            'name.max' => 'Device name must not exceed 100 characters.',
            'device_code.required' => 'Device code is required.',
            'device_code.max' => 'Device code must not exceed 50 characters.',
            'device_code.unique' => 'This device code is already registered.',
            'location.max' => 'Location must not exceed 255 characters.',
            'classroom_id.exists' => 'Selected classroom does not exist.',
            'status.in' => 'Invalid status value.',
            'firmware_version.max' => 'Firmware version must not exceed 20 characters.',
        ];
    }
}
