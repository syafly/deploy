<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model ini.
     * Secara default, Laravel akan mencari 'absensis', jadi kita perlu tentukan secara eksplisit.
     *
     * @var string
     */
    protected $table = 'absensis';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_siswa',
        'tanggal',
        'keterangan',
        'status',
    ];

    /**
     * Casting tipe data untuk kolom tertentu.
     * Kolom 'tanggal' akan secara otomatis diubah menjadi objek Carbon (tanggal/waktu) saat diakses.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
    ];

    // --- Relasi (Relationship) ---

    /**
     * Mendefinisikan relasi: Absensi adalah milik (belongs to) satu Siswa.
     * Asumsi: Model siswa Anda bernama 'Siswa'.
     */
    public function siswa(): BelongsTo
    {
        // Ganti App\Models\Siswa dengan path model Siswa Anda jika berbeda
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}