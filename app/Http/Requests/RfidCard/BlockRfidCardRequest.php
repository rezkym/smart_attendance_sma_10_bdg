<?php

declare(strict_types=1);

namespace App\Http\Requests\RfidCard;

use Illuminate\Foundation\Http\FormRequest;

class BlockRfidCardRequest extends FormRequest
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
            'reason' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.max' => 'Alasan pemblokiran maksimal 500 karakter.',
        ];
    }
}
