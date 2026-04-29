<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiAbsen extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model ini.
     * @var string
     */
    protected $table = 'transaksi_absens';

    /**
     * Nonaktifkan kolom 'updated_at' karena transaksi tidak diupdate.
     * @var bool
     */
    const UPDATED_AT = null;

    /**
     * Kolom yang dapat diisi secara massal.
     * @var array<int, string>
     */
    protected $fillable = [
        'id_siswa',
        'status',
        'waktu_tap',
    ];

    protected $casts = [
        'tap_time' => 'datetime',
    ];
    
    /**
     * Definisikan relasi Many-to-One ke Siswa.
     * Satu transaksi (TimeTransaction) dimiliki oleh satu siswa (Student).
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('waktu_tap', today());
    }
}