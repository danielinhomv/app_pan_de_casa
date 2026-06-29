<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Llamar seeders en orden de dependencias
        $this->call([
            MenuItemSeeder::class,
            RolesSeeder::class,
            UserSeeder::class,
            ClienteSeeder::class,
            ProveedorSeeder::class,
        ]);

        $now = now();
        $today = now()->toDateString();

        // 2. Insertar Datos Base sin IDs fijos (el auto-incremento se gestiona solo)

        DB::table('ingredientes')->insert([
            ['nombre' => 'Harina', 'unidad_medida' => 'kg', 'descripcion' => 'Harina de trigo', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Sal', 'unidad_medida' => 'kg', 'descripcion' => 'Sal de mesa', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Almidon', 'unidad_medida' => 'kg', 'descripcion' => 'Almidón de maíz', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('lote_insumos')->insert([
            ['fecha_ingreso' => $today, 'cantidad_total_x_unidad' => 100, 'cantidad_disponible_x_unidad' => 91.9, 'costo_unitario' => 8, 'costo_lote' => 800, 'proveedor_id' => 1, 'ingrediente_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['fecha_ingreso' => $today, 'cantidad_total_x_unidad' => 20, 'cantidad_disponible_x_unidad' => 20, 'costo_unitario' => 1, 'costo_lote' => 20, 'proveedor_id' => 1, 'ingrediente_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['fecha_ingreso' => $today, 'cantidad_total_x_unidad' => 50, 'cantidad_disponible_x_unidad' => 33.8, 'costo_unitario' => 8, 'costo_lote' => 400, 'proveedor_id' => 1, 'ingrediente_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['fecha_ingreso' => $today, 'cantidad_total_x_unidad' => 20, 'cantidad_disponible_x_unidad' => 20, 'costo_unitario' => 20, 'costo_lote' => 400, 'proveedor_id' => 1, 'ingrediente_id' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('movimiento_insumos')->insert([
            ['fecha' => $today, 'cantidad' => 100, 'tipo_movimiento' => 'entrada', 'motivo_movimiento' => 'compra', 'ingrediente_id' => 2, 'lote_insumo_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['fecha' => $today, 'cantidad' => 20, 'tipo_movimiento' => 'entrada', 'motivo_movimiento' => 'compra', 'ingrediente_id' => 2, 'lote_insumo_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['fecha' => $today, 'cantidad' => 50, 'tipo_movimiento' => 'entrada', 'motivo_movimiento' => 'compra', 'ingrediente_id' => 1, 'lote_insumo_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['fecha' => $today, 'cantidad' => 20, 'tipo_movimiento' => 'entrada', 'motivo_movimiento' => 'compra', 'ingrediente_id' => 3, 'lote_insumo_id' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('productos')->insert([
            ['nombre' => 'Pan Frances', 'unidad_medida' => 'unidad', 'precio_venta' => 0.50, 'descripcion' => 'Pan francés crujiente', 'imagen' => '/images/products/pan1.jpg', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Pan de batalla', 'unidad_medida' => 'unidad', 'precio_venta' => 0.50, 'descripcion' => 'Pan de batalla tradicional', 'imagen' => '/images/products/pan2.jpg', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('recetas')->insert([
            ['cant_x_unidad' => 0.02, 'producto_id' => 1, 'ingrediente_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['cant_x_unidad' => 0.01, 'producto_id' => 1, 'ingrediente_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['cant_x_unidad' => 0.01, 'producto_id' => 2, 'ingrediente_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['cant_x_unidad' => 0.02, 'producto_id' => 2, 'ingrediente_id' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('orden_produccions')->insert([
            ['fecha_creacion' => $today, 'cantidad_a_producir' => 10, 'estado' => 'finalizada', 'operario_id' => null, 'producto_id' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['fecha_creacion' => $today, 'cantidad_a_producir' => 800, 'estado' => 'finalizada', 'operario_id' => null, 'producto_id' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('consumo_insumos')->insert([
            ['cantidad_consumida' => 0.2, 'orden_produccion_id' => 1, 'ingrediente_id' => 1, 'lote_insumo_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['cantidad_consumida' => 0.1, 'orden_produccion_id' => 1, 'ingrediente_id' => 2, 'lote_insumo_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['cantidad_consumida' => 8, 'orden_produccion_id' => 2, 'ingrediente_id' => 2, 'lote_insumo_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['cantidad_consumida' => 16, 'orden_produccion_id' => 2, 'ingrediente_id' => 1, 'lote_insumo_id' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('producto_terminados')->insert([
            ['fecha_produccion' => $today, 'cantidad_producida' => 10, 'orden_produccion_id' => 1, 'producto_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['fecha_produccion' => $today, 'cantidad_producida' => 800, 'orden_produccion_id' => 2, 'producto_id' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('movimiento_productos')->insert([
            ['fecha' => $today, 'cantidad' => 10, 'tipo_movimiento' => 'entrada', 'motivo_movimiento' => null, 'producto_id' => 1, 'producto_terminado_id' => null, 'orden_produccion_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['fecha' => $today, 'cantidad' => 800, 'tipo_movimiento' => 'entrada', 'motivo_movimiento' => null, 'producto_id' => 2, 'producto_terminado_id' => null, 'orden_produccion_id' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}