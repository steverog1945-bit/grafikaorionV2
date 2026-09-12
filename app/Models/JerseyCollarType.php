<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseyCollarType extends Model
{
    protected $table = 'jersey_collar_types';

    protected $fillable = ['name', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
