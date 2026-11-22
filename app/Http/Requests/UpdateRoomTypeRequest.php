<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRoomTypeRequest extends StoreRoomTypeRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = [
            'required',
            'string',
            'max:120',
            Rule::unique('room_types', 'slug')->ignore($this->route('room_type')),
        ];

        return $rules;
    }
}