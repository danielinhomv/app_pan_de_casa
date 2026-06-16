<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'title',
        'route',
        'icon',
        'parent_id',
        'order',
        'is_active',
        'roles',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'roles'     => 'array',
    ];

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

      // Ya no es necesario con el cast, pero lo dejamos por compatibilidad
    public function getRolesArrayAttribute(): array
    {
        return $this->roles ?? [];
    }
}
