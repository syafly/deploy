<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiswaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama'      => 'required|string|max:150',
            'kelas'     => 'required|exists:kelas,id',
            'no_ortu'   => 'required|string|max:50',
            'id_card'   => 'required|string|max:100|unique:siswas,id_card',
        ];
    }

    public function messages()
    {
        return [
            'nama.required'     => 'Nama siswa harus diisi',
            'kelas.required'    => 'Kelas harus dipilih',
            'kelas.exists'      => 'Kelas tidak valid',
            'no_ortu.required'  => 'Nomor HP orang tua harus diisi',
            'id_card.required'  => 'ID Kartu harus diisi',
            'id_card.unique'    => 'ID Kartu sudah digunakan',
        ];
    }
}