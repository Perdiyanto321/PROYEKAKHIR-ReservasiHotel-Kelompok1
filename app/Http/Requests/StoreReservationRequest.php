<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'guest_id' => ['nullable', 'integer', 'exists:users,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'special_requests' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $room = Room::find($this->input('room_id'));
            if ($room && $room->status === 'maintenance') {
                $validator->errors()->add('room_id', 'Kamar ini sedang dalam perbaikan (maintenance).');
                return;
            }

            $bentrok = Reservation::where('room_id', $this->input('room_id'))
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->where(function ($q) {
                    $q->where('check_in_date', '<', $this->input('check_out_date'))
                      ->where('check_out_date', '>', $this->input('check_in_date'));
                })
                ->exists();

            if ($bentrok) {
                $validator->errors()->add('check_in_date', 'Kamar sudah dipesan pada rentang tanggal tersebut.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'room_id.exists' => 'Unit kamar yang dipilih tidak ditemukan.',
            'check_in_date.after_or_equal' => 'Tanggal check-in minimal hari ini.',
            'check_out_date.after' => 'Tanggal check-out harus setelah tanggal check-in.',
        ];
    }

    public function attributes(): array
    {
        return [
            'room_id' => 'unit kamar',
            'guest_id' => 'tamu',
            'check_in_date' => 'tanggal check-in',
            'check_out_date' => 'tanggal check-out',
            'special_requests' => 'permintaan khusus',
        ];
    }
}