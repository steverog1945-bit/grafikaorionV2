<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseyMaterial extends Model
{
    protected $table = 'jersey_materials';

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
