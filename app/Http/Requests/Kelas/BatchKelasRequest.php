<?php

namespace App\Http\Requests\Kelas;

use Illuminate\Foundation\Http\FormRequest;

class BatchKelasRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Sesuaikan dengan auth jika perlu
    }

    public function rules()
    {
        return [
            'added'   => 'sometimes|array',
            'added.*.nama_kelas' => 'required|string|max:255|unique:kelas,nama_kelas',
            'updated' => 'sometimes|array',
            'updated.*.id' => 'required|exists:kelas,id',
            'updated.*.nama_kelas' => 'required|string|max:255',
            'deleted' => 'sometimes|array',
            'deleted.*' => 'exists:kelas,id',
        ];
    }
}