<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class JerseyOrder extends Model
{
    protected $table = 'jersey_orders';

    protected $fillable = [
        'order_number',
        'order_date',
        'id_konsumen',
        'customer_name',
        'customer_phone',
        'team_name',
        'deadline',
        'status',
        'jersey_designer_id',
        'designer_name',
        'notes',
        'total',
        'discount',
        'additional_charge',
        'deposit',
        'status_pembayaran',
        'catatan_referensi_transaksi',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'deadline' => 'date',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'additional_charge' => 'decimal:2',
        'deposit' => 'decimal:2',
    ];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'id_konsumen', 'id_konsumen');
    }

    public function designer()
    {
        return $this->belongsTo(JerseyDesigner::class, 'jersey_designer_id');
    }

    public function lines()
    {
        return $this->hasMany(JerseyOrderLine::class, 'jersey_order_id');
    }

    /**
     * Total order = jumlah subtotal semua line + biaya tambahan - diskon -
     * dipanggil ulang tiap line berubah (tambah/edit/hapus) supaya total
     * order selalu konsisten dengan rincian per-jenis-barangnya.
     */
    public function recalculateTotal(): void
    {
        $subtotalLines = (float) $this->lines()->sum('subtotal');
        $total = max(0, $subtotalLines - (float) $this->discount + (float) $this->additional_charge);
        $this->update(['total' => $total]);
    }

    public static function generateOrderNumber(\DateTimeInterface $date): string
    {
        $prefix = 'JO-' . $date->format('Ymd') . '-';
        $last = DB::table('jersey_orders')
            ->where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->lockForUpdate()
            ->value('order_number');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
