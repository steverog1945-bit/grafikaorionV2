<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseySize extends Model
{
    protected $table = 'jersey_sizes';

    protected $fillable = ['name', 'sort_order', 'is_active', 'price_surcharge'];

    protected $casts = [
        'is_active' => 'boolean',
        'price_surcharge' => 'decimal:2',
    ];
}
