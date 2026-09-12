<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseyCategory extends Model
{
    protected $table = 'jersey_categories';

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
