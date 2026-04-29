<?php

namespace App\Http\Requests\Reservasi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keterangan' => 'required|string|max:255',
            'jam_mulai'  => 'required|date_format:H:i',
            'jam_akhir'  => 'required|date_format:H:i|after:jam_mulai',
        ];
    }

    public function messages(): array
    {
        return [
            'jam_mulai.date_format' => 'Format jam mulai harus HH:MM',
            'jam_akhir.after'       => 'Jam akhir harus setelah jam mulai',
        ];
    }
}