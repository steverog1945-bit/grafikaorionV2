<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Harga extends Model
{
    // Nama tabel jika tidak mengikuti aturan plural (opsional jika nama tabel 'hargas')
    protected $table = 'harga'; 
    protected $primaryKey = 'id_harga'; // Sesuaikan dengan PK di DB Anda

    protected $fillable = [
        'id_produk',
        'id_golongan',
        'range_min',
        'range_max',
        'id_satuan',
        'harga_jual',
        'qty_berlaku',
        'id_satuan_berlaku',
    ];
}