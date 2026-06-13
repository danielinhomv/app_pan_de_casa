<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $fillable = [
        'nombre',
        'tipo',
        'valor',
        'fecha_inicio',
        'fecha_fin',
        'is_active',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    

}
