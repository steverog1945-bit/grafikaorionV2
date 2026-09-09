<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuan'; // Sesuaikan dengan nama tabel di database Anda
    protected $primaryKey = 'id_satuan'; // Sesuaikan jika PK Anda bukan 'id'
    
    protected $fillable = [
        'nama_satuan',
        'tipe_satuan'
    ];
}