<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
    ];

    /**
     * Mendefinisikan relasi: Satu Kelas memiliki banyak Siswa.
     */
    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'id_kelas');
    }

    public function reservasi()
    {
        return $this->hasMany(Kelas::class, 'id_siswa');
    }
}