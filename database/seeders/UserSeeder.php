<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = [
            'cliente' => [
                ['name' => 'Cliente Uno', 'email' => 'cliente1@example.com'],
                ['name' => 'Cliente Dos', 'email' => 'cliente2@example.com'],
            ],
            'encargadoalmacen' => [
                ['name' => 'Encargado de Almacén', 'email' => 'encargadoalmacen@example.com'],
            ],
            'produccion' => [
                ['name' => 'Empleado Uno', 'email' => 'empleado1@example.com'],
                ['name' => 'Empleado Dos', 'email' => 'empleado2@example.com'],
                ['name' => 'Operario Uno', 'email' => 'operario1@example.com'],
                ['name' => 'Operario Dos', 'email' => 'operario2@example.com'],
            ],
            'propietario' => [
                ['name' => 'Propietario Uno', 'email' => 'propietario1@example.com'],
            ],
        ];

        $password = bcrypt('password');

        foreach ($usuarios as $rol => $listaUsuarios) {
            foreach ($listaUsuarios as $datosUsuario) {
                User::factory()->create(array_merge($datosUsuario, [
                    'password' => $password,
                ]))->assignRole($rol);
            }
        }
    }
}