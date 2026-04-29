<?php 

// app/Models/AttendanceRule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    protected $table = 'attendance_rules';
    // Matikan timestamps karena kita tidak membutuhkannya
    public $timestamps = false; 

    protected $fillable = [
        'masuk',
        'istirahat',
        'kembali_istirahat',
        'pulang',
        'status_output',
    ];
}