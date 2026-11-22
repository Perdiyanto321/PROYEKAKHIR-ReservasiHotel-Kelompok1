<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreRoomTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name')) {
            $this->merge([
                'slug' => Str::slug($this->input('name')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'slug' => ['required', 'string', 'max:120', 'unique:room_types,slug'],
            'base_price' => ['required', 'integer', 'min:50000'],
            'capacity' => ['required', 'integer', 'between:1,10'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer', 'exists:facilities,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'slug.unique' => 'Nama tipe kamar menghasilkan slug yang sudah digunakan.',
            'base_price.min' => 'Tarif dasar minimal Rp 50.000.',
            'capacity.between' => 'Kapasitas tamu harus antara :min hingga :max orang.',
            'image_url.url' => 'URL foto kamar harus berformat tautan valid (https://...).',
            'facilities.*.exists' => 'Fasilitas yang dipilih tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama tipe kamar',
            'base_price' => 'tarif per malam',
            'capacity' => 'kapasitas tamu',
            'description' => 'deskripsi kamar',
            'image_url' => 'tautan foto kamar',
            'facilities' => 'fasilitas',
        ];
    }
}