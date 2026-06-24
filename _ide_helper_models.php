<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $fecha
 * @property numeric $monto
 * @property int $cuenta_cobro_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CuentaCobro $cuentaCobro
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Abono newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Abono newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Abono query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Abono whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Abono whereCuentaCobroId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Abono whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Abono whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Abono whereMonto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Abono whereUpdatedAt($value)
 */
	class Abono extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $email_intento
 * @property string|null $nombre_usuario
 * @property string $tipo_evento
 * @property string|null $modulo
 * @property string|null $accion
 * @property string|null $url
 * @property string|null $metodo_http
 * @property int|null $registro_id
 * @property string|null $ip
 * @property string|null $user_agent
 * @property bool $exitoso
 * @property string|null $detalle
 * @property \Illuminate\Support\Carbon $ocurrido_en
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora deHoy()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora delMes()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora loginsExitosos()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora loginsFallidos()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora porModulo(string $modulo)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereAccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereDetalle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereEmailIntento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereExitoso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereMetodoHttp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereModulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereNombreUsuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereOcurridoEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereRegistroId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereTipoEvento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bitacora whereUserId($value)
 */
	class Bitacora extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $nit
 * @property string|null $razon_social
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pedido> $pedidos
 * @property-read int|null $pedidos_count
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Venta> $ventas
 * @property-read int|null $ventas_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereNit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereRazonSocial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereUserId($value)
 */
	class Cliente extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property numeric $cantidad_consumida
 * @property int $orden_produccion_id
 * @property int $ingrediente_id
 * @property int|null $lote_insumo_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingrediente $ingrediente
 * @property-read \App\Models\LoteInsumo|null $loteInsumo
 * @property-read \App\Models\OrdenProduccion $ordenProduccion
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumoInsumo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumoInsumo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumoInsumo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumoInsumo whereCantidadConsumida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumoInsumo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumoInsumo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumoInsumo whereIngredienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumoInsumo whereLoteInsumoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumoInsumo whereOrdenProduccionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumoInsumo whereUpdatedAt($value)
 */
	class ConsumoInsumo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property numeric $saldo_pendiente
 * @property string $fecha_vencimiento
 * @property int $venta_id
 * @property int $cliente_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Venta $venta
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuentaCobro newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuentaCobro newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuentaCobro query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuentaCobro whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuentaCobro whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuentaCobro whereFechaVencimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuentaCobro whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuentaCobro whereSaldoPendiente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuentaCobro whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuentaCobro whereVentaId($value)
 */
	class CuentaCobro extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $cantidad
 * @property numeric $precio_unitario
 * @property numeric $subtotal
 * @property int $venta_id
 * @property int $producto_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Producto $producto
 * @property-read \App\Models\Venta $venta
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta whereCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta wherePrecioUnitario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta whereProductoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetalleVenta whereVentaId($value)
 */
	class DetalleVenta extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $area
 * @property string $cargo
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCargo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereUserId($value)
 */
	class Empleado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre
 * @property string $unidad_medida
 * @property string|null $descripcion
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoteInsumo> $lotes
 * @property-read int|null $lotes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Receta> $recetas
 * @property-read int|null $recetas_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingrediente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingrediente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingrediente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingrediente whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingrediente whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingrediente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingrediente whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingrediente whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingrediente whereUnidadMedida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingrediente whereUpdatedAt($value)
 */
	class Ingrediente extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $fecha_ingreso
 * @property numeric $cantidad_total_x_unidad
 * @property numeric $cantidad_disponible_x_unidad
 * @property numeric $costo_unitario
 * @property numeric $costo_lote
 * @property int|null $proveedor_id
 * @property int $ingrediente_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingrediente $ingrediente
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MovimientoInsumo> $movimientos
 * @property-read int|null $movimientos_count
 * @property-read \App\Models\Proveedor|null $proveedor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo whereCantidadDisponibleXUnidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo whereCantidadTotalXUnidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo whereCostoLote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo whereCostoUnitario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo whereFechaIngreso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo whereIngredienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo whereProveedorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoteInsumo whereUpdatedAt($value)
 */
	class LoteInsumo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string|null $route
 * @property string|null $icon
 * @property int|null $parent_id
 * @property int $order
 * @property bool $is_active
 * @property array<array-key, mixed>|null $roles
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MenuItem> $children
 * @property-read int|null $children_count
 * @property-read array $roles_array
 * @property-read MenuItem|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereUpdatedAt($value)
 */
	class MenuItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $fecha
 * @property numeric $cantidad
 * @property string $tipo_movimiento
 * @property string|null $motivo_movimiento
 * @property int $ingrediente_id
 * @property int|null $lote_insumo_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingrediente $ingrediente
 * @property-read \App\Models\LoteInsumo|null $loteInsumo
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo whereCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo whereIngredienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo whereLoteInsumoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo whereMotivoMovimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo whereTipoMovimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoInsumo whereUpdatedAt($value)
 */
	class MovimientoInsumo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $fecha
 * @property int $cantidad
 * @property string $tipo_movimiento
 * @property string|null $motivo_movimiento
 * @property int $producto_id
 * @property int|null $producto_terminado_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $orden_produccion_id
 * @property-read \App\Models\OrdenProduccion|null $ordenProduccion
 * @property-read \App\Models\Producto $producto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto whereCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto whereMotivoMovimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto whereOrdenProduccionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto whereProductoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto whereProductoTerminadoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto whereTipoMovimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MovimientoProducto whereUpdatedAt($value)
 */
	class MovimientoProducto extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $turno
 * @property string $especialidad
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operario whereEspecialidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operario whereTurno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operario whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operario whereUserId($value)
 */
	class Operario extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $fecha_creacion
 * @property int $cantidad_a_producir
 * @property string $estado
 * @property int|null $operario_id
 * @property int $producto_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConsumoInsumo> $consumoInsumos
 * @property-read int|null $consumo_insumos_count
 * @property-read \App\Models\Operario|null $operario
 * @property-read \App\Models\Producto $producto
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductoTerminado> $productosTerminados
 * @property-read int|null $productos_terminados_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion whereCantidadAProducir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion whereFechaCreacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion whereOperarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion whereProductoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrdenProduccion whereUpdatedAt($value)
 */
	class OrdenProduccion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $page_name
 * @property string|null $page_title
 * @property string $page_path
 * @property int $visit_count
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit wherePageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit wherePagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit wherePageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageVisit whereVisitCount($value)
 */
	class PageVisit extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $fecha
 * @property int $pedido_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pedido $pedido
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PagoAdelantado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PagoAdelantado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PagoAdelantado query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PagoAdelantado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PagoAdelantado whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PagoAdelantado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PagoAdelantado wherePedidoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PagoAdelantado whereUpdatedAt($value)
 */
	class PagoAdelantado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $fecha
 * @property numeric $monto
 * @property string $metodo_pago
 * @property int $venta_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $referencia_externa
 * @property string|null $transaction_id
 * @property string $estado
 * @property \Illuminate\Support\Carbon|null $fecha_pago
 * @property array<array-key, mixed>|null $datos_pago
 * @property-read \App\Models\Venta $venta
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereDatosPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereFechaPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereMetodoPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereMonto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereReferenciaExterna($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pagos whereVentaId($value)
 */
	class Pagos extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $fecha
 * @property string $estado_produccion
 * @property numeric $total
 * @property int $cliente_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente $cliente
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PedidoDetalle> $detalles
 * @property-read int|null $detalles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PedidoRastreo> $rastreos
 * @property-read int|null $rastreos_count
 * @property-read \App\Models\Venta|null $venta
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pedido newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pedido newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pedido query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pedido whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pedido whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pedido whereEstadoProduccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pedido whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pedido whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pedido whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pedido whereUpdatedAt($value)
 */
	class Pedido extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $cantidad
 * @property numeric $precio_unitario
 * @property numeric $subtotal
 * @property int $pedido_id
 * @property int $producto_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pedido $pedido
 * @property-read \App\Models\Producto $producto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle whereCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle wherePedidoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle wherePrecioUnitario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle whereProductoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoDetalle whereUpdatedAt($value)
 */
	class PedidoDetalle extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $cantidad
 * @property numeric $costo_final
 * @property int $pedido_id
 * @property int $producto_base_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pedido $pedido
 * @property-read \App\Models\ProductoBase $productoBase
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoProductoBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoProductoBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoProductoBase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoProductoBase whereCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoProductoBase whereCostoFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoProductoBase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoProductoBase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoProductoBase wherePedidoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoProductoBase whereProductoBaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoProductoBase whereUpdatedAt($value)
 */
	class PedidoProductoBase extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $pedido_id
 * @property int $promocion_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pedido $pedido
 * @property-read \App\Models\Promocion $promocion
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoPromocion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoPromocion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoPromocion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoPromocion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoPromocion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoPromocion wherePedidoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoPromocion wherePromocionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoPromocion whereUpdatedAt($value)
 */
	class PedidoPromocion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property numeric $latitud
 * @property numeric $longitud
 * @property string $hora
 * @property int $pedido_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pedido $pedido
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoRastreo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoRastreo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoRastreo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoRastreo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoRastreo whereHora($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoRastreo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoRastreo whereLatitud($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoRastreo whereLongitud($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoRastreo wherePedidoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PedidoRastreo whereUpdatedAt($value)
 */
	class PedidoRastreo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre
 * @property string $unidad_medida
 * @property numeric $precio_venta
 * @property string|null $descripcion
 * @property string|null $imagen
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DetalleVenta> $detallesVenta
 * @property-read int|null $detalles_venta_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MovimientoProducto> $movimientosProducto
 * @property-read int|null $movimientos_producto_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrdenProduccion> $ordenesProduccion
 * @property-read int|null $ordenes_produccion_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductoTerminado> $productosTerminados
 * @property-read int|null $productos_terminados_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Receta> $recetas
 * @property-read int|null $recetas_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereImagen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto wherePrecioVenta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereUnidadMedida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producto whereUpdatedAt($value)
 */
	class Producto extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre
 * @property numeric $precio
 * @property string|null $descripcion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoBase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoBase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoBase whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoBase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoBase whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoBase wherePrecio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoBase whereUpdatedAt($value)
 */
	class ProductoBase extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $fecha_produccion
 * @property int $cantidad_producida
 * @property int $orden_produccion_id
 * @property int $producto_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OrdenProduccion $ordenProduccion
 * @property-read \App\Models\Producto $producto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoTerminado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoTerminado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoTerminado query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoTerminado whereCantidadProducida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoTerminado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoTerminado whereFechaProduccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoTerminado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoTerminado whereOrdenProduccionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoTerminado whereProductoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductoTerminado whereUpdatedAt($value)
 */
	class ProductoTerminado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre
 * @property string $tipo
 * @property numeric $valor
 * @property string $fecha_inicio
 * @property string $fecha_fin
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion whereFechaFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion whereFechaInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocion whereValor($value)
 */
	class Promocion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $empresa
 * @property string|null $contacto
 * @property bool $estado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proveedor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proveedor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proveedor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proveedor whereContacto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proveedor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proveedor whereEmpresa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proveedor whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proveedor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Proveedor whereUpdatedAt($value)
 */
	class Proveedor extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property numeric $cant_x_unidad
 * @property int $producto_id
 * @property int $ingrediente_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $cantidad
 * @property-read \App\Models\Ingrediente $ingrediente
 * @property-read \App\Models\Producto $producto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receta query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receta whereCantXUnidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receta whereIngredienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receta whereProductoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receta whereUpdatedAt($value)
 */
	class Receta extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property numeric $latitud
 * @property numeric $longitud
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ubicacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ubicacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ubicacion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ubicacion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ubicacion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ubicacion whereLatitud($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ubicacion whereLongitud($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ubicacion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ubicacion whereUserId($value)
 */
	class Ubicacion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $telefono
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $direccion
 * @property string $password
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property-read \App\Models\Cliente|null $cliente
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Operario|null $operario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read string $profile_photo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \App\Models\Ubicacion|null $ubicacion
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTelefono($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $fecha
 * @property numeric $total
 * @property string $tipo_pago
 * @property string $modo_pago
 * @property int $pedido_id
 * @property int $cliente_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\CuentaCobro|null $cuentaCobro
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pagos> $pagos
 * @property-read int|null $pagos_count
 * @property-read \App\Models\Pedido $pedido
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Producto> $productos
 * @property-read int|null $productos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta whereModoPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta wherePedidoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta whereTipoPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Venta whereUpdatedAt($value)
 */
	class Venta extends \Eloquent {}
}

