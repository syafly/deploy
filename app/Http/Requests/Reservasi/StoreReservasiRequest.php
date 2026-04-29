<?php

namespace App\Http\Requests\Reservasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Sesuaikan jika perlu
    }

    public function rules(): array
    {
        return [
            'siswa_ids'         => 'required|array|min:1',
            'siswa_ids.*'       => 'exists:siswas,id',
            'keterangan_global' => 'required|string|max:255',
            'jam_mulai'         => 'required|date',
            'jam_akhir'         => 'required|date|after:jam_mulai',
        ];
    }

    public function messages(): array
    {
        return [
            'siswa_ids.required'   => 'Pilih minimal satu siswa',
            'siswa_ids.*.exists'   => 'Siswa tidak ditemukan',
            'jam_mulai.date_format' => 'Format jam mulai harus HH:MM',
            'jam_akhir.after'       => 'Jam akhir harus setelah jam mulai',
        ];
    }
}