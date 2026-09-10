<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';
    protected $primaryKey = 'id_bahan';
    public $timestamps = false; // Karena di SQL Dump tidak ada created_at

    protected $fillable = [
    'nama_bahan', 
    'kode_bahan', 
    'id_mesin', 
    'id_kategori', 
    'stok', 
    'stok_minimal', // WAJIB ADA
    'id_satuan_stok', 
    'id_satuan_hitung', 
    'nilai_konversi',
    'satuan_stok',
    'harga_rata_rata',
    'id_merk',
    'id_jenis_bahan'
];
public function kategori()
{
    return $this->belongsTo(Kategori::class, 'id_kategori');
}

public function merk()
{
    return $this->belongsTo(Merk::class, 'id_merk');
}

public function jenisBahan()
{
    return $this->belongsTo(JenisBahan::class, 'id_jenis_bahan');
}

public function mesin()
{
    return $this->belongsTo(Mesin::class, 'id_mesin');
}
public function satuanStok() {
    return $this->belongsTo(Satuan::class, 'id_satuan_stok', 'id_satuan');
}

public function satuanHitung() {
    return $this->belongsTo(Satuan::class, 'id_satuan_hitung', 'id_satuan');
}
}