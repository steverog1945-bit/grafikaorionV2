<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'nomor_nota',
        'tipe_order',
        'no_po',
        'id_konsumen',
        'nama_konsumen_manual',
        'id_user_kasir',
        'tgl_order',
        'total_bayar',
        'status_pembayaran',
        'status_nota',
        'alasan_batal_nota',
        'tgl_dibatalkan_nota',
        'id_user_pembatal_nota',
        'status_collecting',
        'tgl_collecting',
        'id_user_collecting',
        'metode_pembayaran',
    ];

    protected $casts = [
        'tgl_order'           => 'datetime',
        'total_bayar'         => 'decimal:2',
        'tgl_dibatalkan_nota' => 'datetime',
        'tgl_collecting'      => 'datetime',
    ];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'id_konsumen', 'id_konsumen');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'id_user_kasir', 'id_user');
    }

    public function pembatal()
    {
        return $this->belongsTo(User::class, 'id_user_pembatal_nota', 'id_user');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'id_user_collecting', 'id_user');
    }

    public function details()
    {
        return $this->hasMany(TransaksiDetail::class, 'id_transaksi', 'id_transaksi');
    }

    public function pembayaran()
    {
        return $this->hasMany(PembayaranRiwayat::class, 'id_transaksi', 'id_transaksi');
    }

    public function deposits()
    {
        return $this->hasMany(KonsumenDeposit::class, 'id_transaksi', 'id_transaksi');
    }
}
