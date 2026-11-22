<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('room_number')) {
            $this->merge([
                'room_number' => strtoupper(trim($this->input('room_number'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'room_number' => ['required', 'string', 'max:10', 'unique:rooms,room_number'],
            'floor' => ['required', 'integer', 'between:1,50'],
            'status' => ['required', 'in:tersedia,terisi,maintenance'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'room_number.unique' => 'Nomor kamar :input sudah terdaftar.',
            'room_type_id.exists' => 'Tipe kamar tidak valid.',
            'status.in' => 'Status kamar harus tersedia, terisi, atau maintenance.',
        ];
    }

    public function attributes(): array
    {
        return [
            'room_type_id' => 'tipe kamar',
            'room_number' => 'nomor kamar',
            'floor' => 'lantai',
            'status' => 'status kamar',
            'notes' => 'catatan',
        ];
    }
}