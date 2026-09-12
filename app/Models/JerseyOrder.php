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
        'jersey_customer_id',
        'customer_name',
        'customer_phone',
        'team_name',
        'jersey_product_type_id',
        'jersey_category_id',
        'deadline',
        'status',
        'order_type',
        'jersey_designer_id',
        'designer_name',
        'notes',
        'total',
        'discount',
        'additional_charge',
        'price_breakdown',
        'jersey_price_setting_id',
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
        'price_breakdown' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(JerseyCustomer::class, 'jersey_customer_id');
    }

    public function productType()
    {
        return $this->belongsTo(JerseyProductType::class, 'jersey_product_type_id');
    }

    public function category()
    {
        return $this->belongsTo(JerseyCategory::class, 'jersey_category_id');
    }

    public function designer()
    {
        return $this->belongsTo(JerseyDesigner::class, 'jersey_designer_id');
    }

    public function priceSetting()
    {
        return $this->belongsTo(JerseyPriceSetting::class, 'jersey_price_setting_id');
    }

    public function items()
    {
        return $this->hasMany(JerseyOrderItem::class, 'jersey_order_id');
    }

    public function sizeQuantities()
    {
        return $this->hasMany(JerseyOrderSizeQuantity::class, 'jersey_order_id');
    }

    /**
     * Format JO-YYYYMMDD-### (reset urut per hari) - dipakai berpasangan
     * dengan lockForUpdate di JerseyOrderController::store() supaya aman
     * dari dua order dibuat bersamaan pada hari yang sama.
     */
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
