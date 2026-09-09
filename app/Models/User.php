<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'id_karyawan', 'username', 'password', 'id_role',
    ];
    public function karyawan()
    {
    return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }
    // Relasi ke tabel Role
    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role');
    }
}