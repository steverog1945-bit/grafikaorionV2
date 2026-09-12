<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseyOrderItem extends Model
{
    protected $table = 'jersey_order_items';

    protected $fillable = [
        'jersey_order_line_id',
        'player_name',
        'player_number',
        'size',
        'gender',
        'sleeve',
        'collar',
        'notes',
    ];

    public function line()
    {
        return $this->belongsTo(JerseyOrderLine::class, 'jersey_order_line_id');
    }
}
