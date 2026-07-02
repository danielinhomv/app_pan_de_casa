<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        MenuItem::truncate();

        $items = [
            [
                'title'     => 'Dashboard',
                'route'     => 'dashboard',
                'icon'      => 'fas fa-home',
                'order'     => 1,
                'is_active' => true,
                'roles'     => ['propietario', 'produccion'],
            ],
            [
                'title'     => 'Inventario',
                'route'     => 'inventory',
                'icon'      => 'fas fa-boxes',
                'order'     => 2,
                'is_active' => true,
                'roles'     => ['propietario', 'encargadoalmacen'],
            ],
            [
                'title'     => 'Producción',
                'route'     => 'production',
                'icon'      => 'fas fa-bread-slice',
                'order'     => 3,
                'is_active' => true,
                'roles'     => ['propietario', 'produccion'],
            ],
            [
                'title'     => 'Métodos y Estadísticas',
                'route'     => 'metodos.index',
                'icon'      => 'fas fa-calculator',
                'order'     => 4,
                'is_active' => true,
                'roles'     => ['propietario', 'encargadoalmacen'],
            ],
            [
                'title'     => 'Catálogo',
                'route'     => 'catalogo.index',
                'icon'      => 'fas fa-book',
                'order'     => 5,
                'is_active' => true,
                'roles'     => ['propietario', 'cliente'],
            ],
            [
                'title'     => 'Mis Pedidos',
                'route'     => 'cliente.pedidos.index',
                'icon'      => 'fas fa-clipboard-list',
                'order'     => 6,
                'is_active' => true,
                'roles'     => ['propietario', 'cliente'],
            ],
            [
                'title'     => 'Pedidos',
                'route'     => 'pedidos.index',
                'icon'      => 'fas fa-truck',
                'order'     => 7,
                'is_active' => true,
                'roles'     => ['propietario', 'encargadoalmacen'],
            ],
            [
                'title'     => 'Bitácora',
                'route'     => 'bitacoras.index',
                'icon'      => 'fas fa-history',
                'order'     => 8,
                'is_active' => true,
                'roles'     => ['propietario'],
            ],
            [
                'title'     => 'Usuarios',
                'route'     => 'usuarios.index',
                'icon'      => 'fas fa-users',
                'order'     => 9,
                'is_active' => true,
                'roles'     => ['propietario'],
            ],
        ];

        foreach ($items as $item) {
            MenuItem::create($item);
        }
    }
}
