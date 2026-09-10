<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finishing extends Model
{
    protected $table = 'finishing';
    protected $primaryKey = 'id_finishing';

    protected $fillable = [
        'nama_finishing',
        'harga_default',
        'id_satuan',
        'id_kategori',
        'id_mesin',
        'metode_hitung',
        'keterangan',
    ];

    public function mesin()
    {
        return $this->belongsTo(Mesin::class, 'id_mesin');
    }
}