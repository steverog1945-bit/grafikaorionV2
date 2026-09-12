<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseyComponent extends Model
{
    protected $table = 'jersey_components';

    protected $fillable = ['name', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
