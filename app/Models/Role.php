<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'id_role';
    public $timestamps = false;

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_permissions', 'id_role', 'id_menu');
    }
}