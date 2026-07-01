<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemSeederClean extends Seeder
{
    /**
     * Menú limpio - Solo items principales en sidebar
     * Las rutas CRUD (create, edit, delete, show) existen para autorización
     * pero no se muestran en el menú lateral (se acceden vía botones en las vistas)
     */
    public function run(): void
    {
        DB::table('menu_items')->truncate();

        $menuItems = [
            // ==============================================
            // PROPIETARIO (role_id: 1) - MENÚ LIMPIO
            // ==============================================
            ['etiqueta' => 'Dashboard', 'ruta_laravel' => 'dashboard', 'icono' => 'FaGaugeHigh', 'orden' => 10, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Productos', 'ruta_laravel' => 'productos.index', 'icono' => 'FaBox', 'orden' => 20, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Categorías', 'ruta_laravel' => 'categorias.index', 'icono' => 'FaTags', 'orden' => 30, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Promociones', 'ruta_laravel' => 'promociones.index', 'icono' => 'FaGift', 'orden' => 40, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ventas', 'ruta_laravel' => 'ventas.index', 'icono' => 'FaCar', 'orden' => 50, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Compras', 'ruta_laravel' => 'compras.index', 'icono' => 'FaCartPlus', 'orden' => 55, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Inventario', 'ruta_laravel' => 'inventarios.index', 'icono' => 'FaWarehouse', 'orden' => 57, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Pedidos', 'ruta_laravel' => 'pedidos.index', 'icono' => 'FaClipboardList', 'orden' => 60, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Usuarios', 'ruta_laravel' => 'usuarios.index', 'icono' => 'FaUsers', 'orden' => 70, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Créditos', 'ruta_laravel' => 'creditos.index', 'icono' => 'FaCreditCard', 'orden' => 80, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Métodos de Pago', 'ruta_laravel' => 'metodos-pago.index', 'icono' => 'FaWallet', 'orden' => 85, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Pagos', 'ruta_laravel' => 'pagos.index', 'icono' => 'FaMoneyBillWave', 'orden' => 90, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Reportes', 'ruta_laravel' => 'reportes.index', 'icono' => 'FaChartLine', 'orden' => 100, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Bitácora', 'ruta_laravel' => 'bitacora.index', 'icono' => 'FaBook', 'orden' => 105, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Proveedores (solo item principal si se desea mostrar) - opcional
            ['etiqueta' => 'Proveedores', 'ruta_laravel' => 'proveedores.index', 'icono' => 'FaFileChart', 'orden' => 110, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Rutas CRUD para autorización (NO visibles en menú - para Policies)
            ['etiqueta' => 'Crear Producto', 'ruta_laravel' => 'productos.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Guardar Producto', 'ruta_laravel' => 'productos.store', 'icono' => '💾', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Producto', 'ruta_laravel' => 'productos.edit', 'icono' => '✏️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Actualizar Producto', 'ruta_laravel' => 'productos.update', 'icono' => '🔄', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Producto', 'ruta_laravel' => 'productos.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Producto', 'ruta_laravel' => 'productos.destroy', 'icono' => '🗑️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Crear Categoría', 'ruta_laravel' => 'categorias.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Guardar Categoría', 'ruta_laravel' => 'categorias.store', 'icono' => '💾', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Categoría', 'ruta_laravel' => 'categorias.edit', 'icono' => '✏️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Actualizar Categoría', 'ruta_laravel' => 'categorias.update', 'icono' => '🔄', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Categoría', 'ruta_laravel' => 'categorias.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Categoría', 'ruta_laravel' => 'categorias.destroy', 'icono' => '🗑️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Crear Promoción', 'ruta_laravel' => 'promociones.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Guardar Promoción', 'ruta_laravel' => 'promociones.store', 'icono' => '💾', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Promoción', 'ruta_laravel' => 'promociones.edit', 'icono' => '✏️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Actualizar Promoción', 'ruta_laravel' => 'promociones.update', 'icono' => '🔄', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Promoción', 'ruta_laravel' => 'promociones.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Promoción', 'ruta_laravel' => 'promociones.destroy', 'icono' => '🗑️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Crear Proveedor', 'ruta_laravel' => 'proveedores.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Guardar Proveedor', 'ruta_laravel' => 'proveedores.store', 'icono' => '💾', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Proveedor', 'ruta_laravel' => 'proveedores.edit', 'icono' => '✏️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Actualizar Proveedor', 'ruta_laravel' => 'proveedores.update', 'icono' => '🔄', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Proveedor', 'ruta_laravel' => 'proveedores.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Proveedor', 'ruta_laravel' => 'proveedores.destroy', 'icono' => '🗑️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Crear Compra', 'ruta_laravel' => 'compras.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Guardar Compra', 'ruta_laravel' => 'compras.store', 'icono' => '💾', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Compra', 'ruta_laravel' => 'compras.edit', 'icono' => '✏️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Actualizar Compra', 'ruta_laravel' => 'compras.update', 'icono' => '🔄', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Compra', 'ruta_laravel' => 'compras.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Compra', 'ruta_laravel' => 'compras.destroy', 'icono' => '🗑️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Crear Usuario', 'ruta_laravel' => 'usuarios.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Guardar Usuario', 'ruta_laravel' => 'usuarios.store', 'icono' => '💾', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Usuario', 'ruta_laravel' => 'usuarios.edit', 'icono' => '✏️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Actualizar Usuario', 'ruta_laravel' => 'usuarios.update', 'icono' => '🔄', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Usuario', 'ruta_laravel' => 'usuarios.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Usuario', 'ruta_laravel' => 'usuarios.destroy', 'icono' => '🗑️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Crear Crédito', 'ruta_laravel' => 'creditos.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Crédito', 'ruta_laravel' => 'creditos.edit', 'icono' => '✏️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Actualizar Crédito', 'ruta_laravel' => 'creditos.update', 'icono' => '🔄', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Crédito', 'ruta_laravel' => 'creditos.destroy', 'icono' => '🗑️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Registrar Pago', 'ruta_laravel' => 'pagos.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Guardar Pago', 'ruta_laravel' => 'pagos.store', 'icono' => '💾', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Pago', 'ruta_laravel' => 'pagos.edit', 'icono' => '✏️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Actualizar Pago', 'ruta_laravel' => 'pagos.update', 'icono' => '🔄', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Eliminar Pago', 'ruta_laravel' => 'pagos.destroy', 'icono' => '🗑️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['etiqueta' => 'Ver Pedido', 'ruta_laravel' => 'pedidos.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Crear Pedido', 'ruta_laravel' => 'pedidos.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Guardar Pedido', 'ruta_laravel' => 'pedidos.store', 'icono' => '💾', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Editar Pedido', 'ruta_laravel' => 'pedidos.edit', 'icono' => '✏️', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Actualizar Pedido', 'ruta_laravel' => 'pedidos.update', 'icono' => '🔄', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Confirmar Pedido', 'ruta_laravel' => 'pedidos.confirmar', 'icono' => '✅', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Cancelar Pedido', 'ruta_laravel' => 'pedidos.cancelar', 'icono' => '❌', 'orden' => 999, 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ==============================================
            // VENDEDOR (role_id: 2) - MENÚ LIMPIO
            // ==============================================
            ['etiqueta' => 'Dashboard', 'ruta_laravel' => 'dashboard', 'icono' => 'FaGaugeHigh', 'orden' => 10, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Productos', 'ruta_laravel' => 'productos.index', 'icono' => 'FaBox', 'orden' => 20, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ventas', 'ruta_laravel' => 'ventas.index', 'icono' => 'FaCar', 'orden' => 30, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Pedidos', 'ruta_laravel' => 'pedidos.index', 'icono' => 'FaClipboardList', 'orden' => 40, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Créditos', 'ruta_laravel' => 'creditos.index', 'icono' => 'FaCreditCard', 'orden' => 50, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Pagos', 'ruta_laravel' => 'pagos.index', 'icono' => 'FaMoneyBillWave', 'orden' => 60, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Reportes', 'ruta_laravel' => 'reportes.index', 'icono' => 'FaChartLine', 'orden' => 70, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],

            // Rutas para autorización Vendedor
            ['etiqueta' => 'Ver Producto', 'ruta_laravel' => 'productos.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Crear Crédito', 'ruta_laravel' => 'creditos.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Registrar Pago', 'ruta_laravel' => 'pagos.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Pedido', 'ruta_laravel' => 'pedidos.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Crear Pedido', 'ruta_laravel' => 'pedidos.create', 'icono' => '➕', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Confirmar Pedido', 'ruta_laravel' => 'pedidos.confirmar', 'icono' => '✅', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Cancelar Pedido', 'ruta_laravel' => 'pedidos.cancelar', 'icono' => '❌', 'orden' => 999, 'role_id' => 2, 'created_at' => now(), 'updated_at' => now()],

            // ==============================================
            // CLIENTE (role_id: 3) - MENÚ LIMPIO
            // ==============================================
            ['etiqueta' => 'Dashboard', 'ruta_laravel' => 'dashboard', 'icono' => 'FaGaugeHigh', 'orden' => 10, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Productos', 'ruta_laravel' => 'productos.index', 'icono' => 'FaBox', 'orden' => 20, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Promociones', 'ruta_laravel' => 'promociones.index', 'icono' => 'FaGift', 'orden' => 30, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Mi Carrito', 'ruta_laravel' => 'carritos.index', 'icono' => 'FaCartShopping', 'orden' => 40, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Mis Pedidos', 'ruta_laravel' => 'mis-pedidos.index', 'icono' => 'FaClipboardList', 'orden' => 50, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Mis Créditos', 'ruta_laravel' => 'mis-creditos.index', 'icono' => 'FaCreditCard', 'orden' => 60, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Mis Pagos', 'ruta_laravel' => 'mis-pagos.index', 'icono' => 'FaMoneyBillWave', 'orden' => 70, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],

            // Rutas para autorización Cliente
            ['etiqueta' => 'Ver Producto', 'ruta_laravel' => 'productos.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Promoción', 'ruta_laravel' => 'promociones.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['etiqueta' => 'Ver Mis Pedidos', 'ruta_laravel' => 'mis-pedidos.show', 'icono' => '👁️', 'orden' => 999, 'role_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('menu_items')->insert($menuItems);

        echo "\n✅ Menú limpio insertado correctamente.\n";
        echo "- Propietario: 11 módulos principales + permisos CRUD completos (create, store, edit, update, show, destroy)\n";
        echo "- Vendedor: 7 módulos (Dashboard, Productos, Ventas, Pedidos, Créditos, Pagos, Reportes)\n";
        echo "- Cliente: 7 módulos (Dashboard, Productos, Promociones, Carrito, Mis Pedidos, Mis Créditos, Mis Pagos)\n";
        echo "- Las rutas CRUD existen para autorización pero NO se muestran en el menú lateral.\n";
    }
}
