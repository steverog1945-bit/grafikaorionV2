<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseyOrderSizeQuantity extends Model
{
    protected $table = 'jersey_order_size_quantities';

    protected $fillable = ['jersey_order_id', 'jersey_size_id', 'quantity'];

    public function order()
    {
        return $this->belongsTo(JerseyOrder::class, 'jersey_order_id');
    }

    public function size()
    {
        return $this->belongsTo(JerseySize::class, 'jersey_size_id');
    }
}
