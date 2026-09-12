<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseyCustomer extends Model
{
    protected $table = 'jersey_customers';

    protected $fillable = ['name', 'phone', 'address', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
