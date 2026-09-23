<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KedatanganBahan extends Model
{
    protected $table = 'kedatangan_bahan';
    protected $primaryKey = 'id_kedatangan';
    public $timestamps = false;

    // Sebelumnya $guarded=[] dan $fillable dideklarasikan sekaligus - kontradiktif,
    // karena $guarded=[] membuat SEMUA kolom bisa di-mass-assign (whitelist
    // $fillable jadi tidak berfungsi). Sekarang cuma $fillable yang dipakai.
    protected $fillable = [
        'no_surat_jalan',
        'id_supplier',
        'id_gudang',
        'tgl_datang',
        'status',
        'keterangan'
    ];
protected $casts = [
        'tgl_datang' => 'date',
        'created_at' => 'datetime', 
    ];
    public function supplier() {
    return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
}

public function detail() {
    return $this->hasMany(KedatanganBahanDetail::class, 'id_kedatangan', 'id_kedatangan');
}

public function pembelian() {
    return $this->hasOne(PembelianBahan::class, 'id_kedatangan', 'id_kedatangan');
}
    // Route Model Binding
    public function getRouteKeyName()
    {
        return 'id_kedatangan';
    }
    public function gudang()
{
    return $this->belongsTo(Gudang::class, 'id_gudang', 'id_gudang');
}
}