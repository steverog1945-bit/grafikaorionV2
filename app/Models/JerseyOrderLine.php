<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseyOrderLine extends Model
{
    protected $table = 'jersey_order_lines';

    protected $fillable = [
        'jersey_order_id',
        'jersey_product_type_id',
        'jersey_category_id',
        'nama_barang',
        'bahan',
        'keterangan',
        'thumbnail',
        'order_type',
        'qty',
        'harga_satuan',
        'subtotal',
        'jersey_price_setting_id',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(JerseyOrder::class, 'jersey_order_id');
    }

    public function productType()
    {
        return $this->belongsTo(JerseyProductType::class, 'jersey_product_type_id');
    }

    public function category()
    {
        return $this->belongsTo(JerseyCategory::class, 'jersey_category_id');
    }

    public function priceSetting()
    {
        return $this->belongsTo(JerseyPriceSetting::class, 'jersey_price_setting_id');
    }

    public function items()
    {
        return $this->hasMany(JerseyOrderItem::class, 'jersey_order_line_id');
    }

    public function sizeQuantities()
    {
        return $this->hasMany(JerseyOrderSizeQuantity::class, 'jersey_order_line_id');
    }

    public function materials()
    {
        return $this->hasMany(JerseyOrderLineMaterial::class, 'jersey_order_line_id');
    }
}
