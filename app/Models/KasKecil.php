<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasKecil extends Model
{
    protected $table = 'kas_kecil';

    protected $fillable = ['tanggal', 'id_kategori', 'keterangan', 'nominal', 'bukti_path', 'id_user'];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriKasKecil::class, 'id_kategori', 'id_kategori_kas_kecil');
    }
}
