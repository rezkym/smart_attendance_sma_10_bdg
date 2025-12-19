<?php

declare(strict_types=1);

namespace App\Http\Requests\RfidCard;

use App\Enums\CardStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRfidCardRequest extends FormRequest
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
            'card_uid' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rfid_cards', 'card_uid'),
            ],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'status' => [
                'required',
                'integer',
                Rule::in(CardStatus::values()),
            ],
            'issued_at' => [
                'required',
                'date',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:issued_at',
            ],
            'notes' => [
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
            'card_uid.required' => 'UID kartu wajib diisi.',
            'card_uid.max' => 'UID kartu maksimal 50 karakter.',
            'card_uid.unique' => 'UID kartu sudah terdaftar dalam sistem.',
            'user_id.exists' => 'Pengguna tidak valid.',
            'status.required' => 'Status kartu wajib dipilih.',
            'status.in' => 'Status kartu tidak valid.',
            'issued_at.required' => 'Tanggal terbit wajib diisi.',
            'issued_at.date' => 'Format tanggal terbit tidak valid.',
            'expires_at.date' => 'Format tanggal kadaluarsa tidak valid.',
            'expires_at.after' => 'Tanggal kadaluarsa harus setelah tanggal terbit.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
