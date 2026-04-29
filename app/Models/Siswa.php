<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswas';

    protected $fillable = [
        'nama',
        'id_kelas',
        'no_orangtua',
        'id_card',
    ];

    /**
     * Mendefinisikan relasi: Siswa adalah milik (belongs to) satu Kelas.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function transaksi_absen(): HasMany
    {
        return $this->hasMany(TransaksiAbsen::class, 'id_siswa');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'id_siswa');
    }

    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'id_siswa');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when($filters['search'] ?? null, function (Builder $query, $search) {
            $query->where('nama', 'like', '%' . $search . '%');
        })->when($filters['kelas'] ?? null, function (Builder $query, $kelas) {
            $query->whereHas('kelas', function (Builder $q) use ($kelas) {
                $q->where('id', $kelas);
            });
        });
    }
}