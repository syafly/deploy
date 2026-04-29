<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Login extends Authenticatable
{
    use HasFactory;

    // Nama tabel yang terkait
    protected $table = 'logins';

    // Jika tabel tidak menggunakan timestamps
    public $timestamps = false;

    // Kolom yang dapat diisi
    protected $fillable = [
        'id_user',
        'username',
        'password'
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function isAdmin() {
        return optional($this->user)->role === 'admin';
    }
}
