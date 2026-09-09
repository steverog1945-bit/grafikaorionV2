<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier'; // WAJIB kalau bukan plural
    protected $primaryKey = 'id_supplier';
    public $timestamps = false;

    protected $guarded = [];
}
