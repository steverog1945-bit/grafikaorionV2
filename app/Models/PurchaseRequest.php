<?php

namespace App\Models;

use App\Support\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use Auditable;

    protected $table = 'purchase_requests';

    protected $fillable = ['nomor', 'tanggal', 'tgl_dibutuhkan', 'status', 'catatan', 'id_user_pembuat'];

    protected $casts = ['tanggal' => 'date', 'tgl_dibutuhkan' => 'date'];

    public function details()
    {
        return $this->hasMany(PurchaseRequestDetail::class, 'id_purchase_request');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'id_user_pembuat', 'id_user');
    }
}
