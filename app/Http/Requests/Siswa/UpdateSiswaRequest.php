<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiswaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama'          => 'required|string|max:150',
            'id_kelas'      => 'required|exists:kelas,id',
            'id_card'       => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('siswas', 'id_card')->ignore($this->route('id')),
            ],
            'no_orangtua'   => 'required|string|max:50',
        ];
    }
}