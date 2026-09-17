<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKasKecil extends Model
{
    protected $table = 'kategori_kas_kecil';
    protected $primaryKey = 'id_kategori_kas_kecil';

    protected $fillable = ['nama_kategori'];

    public function kasKecil()
    {
        return $this->hasMany(KasKecil::class, 'id_kategori', 'id_kategori_kas_kecil');
    }
}
