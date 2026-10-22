<?php

namespace App\Models;

use App\Support\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    use Auditable;

    // password TIDAK pernah masuk audit_logs (hash pun tidak perlu
    // tersimpan di sana - riwayat "kapan password diganti" cukup dari
    // event 'updated'-nya sendiri, isinya tidak relevan/berisiko disimpan).
    protected array $hiddenFromAudit = ['password', 'remember_token'];

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    // 'password' dan 'id_role' SENGAJA tidak masuk $fillable (kolom
    // sensitif/privilege) - diisi lewat assignment eksplisit di
    // UserController, bukan mass-assignment, supaya endpoint baru yang
    // ceroboh (mis. pakai $request->all()) tidak bisa diam-diam mengubah
    // role/password lewat form field yang tidak seharusnya.
    protected $fillable = [
        'id_karyawan', 'username', 'api_token',
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