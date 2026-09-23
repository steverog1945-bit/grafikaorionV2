<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PembelianBahan extends Model
{
    use SoftDeletes;

    protected $table = 'pembelian_bahan';
    protected $primaryKey = 'id_pembelian';

    protected $fillable = [
        'id_kedatangan',
        'no_faktur',
        'tgl_transaksi',
        'tipe_pembelian',
        'jatuh_tempo_hari',
        'jatuh_tempo_tgl',
        'status_pajak',
        'include_pajak',
        'grand_total'
    ];

    public function detail()
    {
        return $this->hasMany(PembelianBahanDetail::class,'id_pembelian');
    }

    public function kedatangan()
    {
        return $this->belongsTo(KedatanganBahan::class,'id_kedatangan');
    }
}