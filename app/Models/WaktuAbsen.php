<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaktuAbsen extends Model
{
    use HasFactory;
    
    protected $table = 'waktu_absen';

    protected $fillable = [
        'status',
        'from',
        'to',
    ];

    // Nonaktifkan timestamps jika tidak diperlukan (meskipun sudah ada di migrasi)
    // public $timestamps = false; 

    // protected $casts = [
    //     'from' => 'time', // Laravel tidak memiliki built-in 'time' cast, ini mungkin hanya string. 
    //     'to' => 'time',   // Jika Anda menggunakan custom cast, sesuaikan di sini.
    // ];
}