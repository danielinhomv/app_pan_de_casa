<?php

namespace App\Http\Controllers;

use App\Models\ProductoBase;
use Inertia\Inertia;

abstract class Controller
{
    public function index()
    {
        return "hola";
        $productos = ProductoBase::all();
        return Inertia::render('Inventario/index', [
            'productos' => $productos,
        ]);
    }
}
