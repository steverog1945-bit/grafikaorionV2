<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseyProductType extends Model
{
    protected $table = 'jersey_product_types';

    protected $fillable = ['name', 'is_active', 'has_sleeve_collar'];

    protected $casts = ['is_active' => 'boolean', 'has_sleeve_collar' => 'boolean'];
}
