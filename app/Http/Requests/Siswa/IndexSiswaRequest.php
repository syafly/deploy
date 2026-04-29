<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class IndexSiswaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'kelas'  => 'nullable|exists:kelas,id',
            'tanggal' => 'nullable|date',
        ];
    }
}