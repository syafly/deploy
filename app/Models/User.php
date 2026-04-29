<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Model
{
    use HasFactory;

    protected $table = 'users'; // Nama tabel
    public $incrementing = true;
    public $timestamps = false;
    
    protected $fillable = [
        'nama',
        'role'
    ];

    // Relasi ke Login
    public function login()
    {
        return $this->hasOne(Login::class, 'id_user');
    }
}
