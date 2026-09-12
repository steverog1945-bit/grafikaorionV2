<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseyDesigner extends Model
{
    protected $table = 'jersey_designers';

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
