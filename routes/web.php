<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProductoVarianteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoOnlineController;
use App\Http\Controllers\PagoCuotaController;
use App\Http\Controllers\MisPedidosController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\GlobalSearchController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ============================================
// RUTAS PÚBLICAS - SIN AUTENTICACIÓN
// ============================================
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Cargar rutas públicas separadas (tienda, etc.)
require __DIR__ . '/public.php';

Route::get('/buscar-global', GlobalSearchController::class)->name('search.global');

Route::get('/test-simple', function() {
    return response()->json(['message' => 'Test simple funciona']);
});

// ============================================
// RUTAS AUTENTICADAS
// ============================================
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    // API para obtener menú dinámico
    Route::get('/api/menu', [MenuController::class, 'getMenuItems'])->name('api.menu');
    
    // API de búsqueda global
    Route::get('/api/search/all', [SearchController::class, 'search'])->name('api.search');
    
    // Dashboard - Todos los roles autenticados
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil del usuario - vista para mostrar información
    Route::get('/mi-perfil', function (Request $request) {
        return Inertia::render('Profile/Show');
    })->name('perfil.show');

    // Notificaciones del cliente/usuario autenticado
    Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notificaciones/no-leidas', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('/notificaciones/{id}/leida', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notificaciones/leidas-todas', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Perfil del usuario - edición de datos personales
    Route::get('/mi-perfil/editar-informacion', function () {
        return Inertia::render('Profile/EditPersonal');
    })->name('perfil.edit');

    // Configuraciones del usuario
    Route::get('/user/settings', function (Request $request) {
        $sessions = [];

        if (config('session.driver') === 'database') {
            $sessions = DB::table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderByDesc('last_activity')
                ->get()
                ->map(function ($session) use ($request) {
                    $userAgent = $session->user_agent ?? '';

                    $platform = str_contains($userAgent, 'Windows')
                        ? 'Windows'
                        : (str_contains($userAgent, 'Mac')
                            ? 'Mac'
                            : (str_contains($userAgent, 'Linux')
                                ? 'Linux'
                                : 'Desconocido'));

                    $browser = str_contains($userAgent, 'Edg')
                        ? 'Edge'
                        : (str_contains($userAgent, 'Chrome')
                            ? 'Chrome'
                            : (str_contains($userAgent, 'Firefox')
                                ? 'Firefox'
                                : (str_contains($userAgent, 'Safari')
                                    ? 'Safari'
                                    : 'Desconocido')));

                    return [
                        'agent' => [
                            'platform' => $platform,
                            'browser' => $browser,
                            'is_desktop' => ! preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent),
                        ],
                        'ip_address' => $session->ip_address ?? 'N/A',
                        'is_current_device' => $session->id === $request->session()->getId(),
                        'last_active' => $session->last_activity
                            ? Carbon::createFromTimestamp($session->last_activity)->diffForHumans()
                            : 'Desconocida',
                    ];
                })
                ->all();
        }

        return Inertia::render('Profile/Settings', [
            'confirmsTwoFactorAuthentication' => true,
            'sessions' => $sessions,
        ]);
    })->name('profile.settings');

    Route::post('/user/settings/preferences', function (Request $request) {
        $validated = $request->validate([
            'theme' => ['sometimes', 'nullable', 'string', 'in:ninos,jovenes,adultos'],
            'mode' => ['sometimes', 'string', 'in:dia,noche'],
            'font_size' => ['sometimes', 'string', 'in:small,normal,large'],
            'contrast' => ['sometimes', 'string', 'in:normal,high'],
        ]);

        if ($request->exists('theme') && $request->input('theme') === null) {
            $validated['theme'] = '';
        }

        $request->user()->fill($validated);
        $request->user()->save();

        return response()->json(['status' => 'ok']);
    })->name('user.settings.preferences');

    // ============================================
    // GESTIÓN DE CARRITO Y PEDIDOS
    // ============================================
    
    // Carrito - Todos los usuarios autenticados
    Route::get('/mi-carrito', [CarritoController::class, 'index'])->name('carritos.index');
    Route::post('/carrito/agregar', [CarritoController::class, 'store'])->name('carritos.store');
    Route::put('/carrito/{carritoDetalle}', [CarritoController::class, 'update'])->name('carritos.update');
    Route::delete('/carrito/{carritoDetalle}', [CarritoController::class, 'destroy'])->name('carritos.destroy');
    
    // Pedidos - Checkout y confirmación
    Route::get('/checkout', [PedidoController::class, 'checkout'])->name('pedidos.checkout');
    Route::post('/pedidos/procesar', [PedidoController::class, 'store'])->name('pedidos.store');
    Route::get('/pedidos/{venta}/confirmacion', [PedidoController::class, 'confirmacion'])->name('pedidos.confirmacion');

    // Carrito legacy (antiguo sistema)
    Route::prefix('carrito')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'get'])->name('get');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::put('/{id}', [CartController::class, 'update'])->name('update');
        Route::delete('/{id}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/', [CartController::class, 'clear'])->name('clear');
        Route::post('/sync', [CartController::class, 'sync'])->name('sync');
        // Pagos QR para cuotas de créditos - permitir Cliente, Propietario y Vendedor
        Route::post('/cuotas/{id}/generar-qr', [PagoCuotaController::class, 'generarQRCuota'])
            ->middleware('role:Cliente,Propietario,Vendedor')
            ->name('cuotas.generar-qr');
        Route::get('/pagos/{id}/estado', [PagoCuotaController::class, 'verificarEstadoPago'])
            ->middleware('role:Cliente,Propietario,Vendedor')
            ->name('pagos.verificar-estado');
    });

    // Página del carrito
    Route::get('/carrito/ver', function () {
        return Inertia::render('Cart/Index');
    })->name('cart.page');

    // Ventas
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/{id}/detalles', [VentaController::class, 'show'])->name('ventas.show');
    Route::post('/ventas/contado', [VentaController::class, 'storeVentaContado'])->name('ventas.contado');
    Route::post('/ventas/credito', [VentaController::class, 'storeVentaCredito'])->middleware('role:Propietario,Vendedor')->name('ventas.credito');

    // Boletas/Facturas
    Route::get('/ventas/{id}/boleta', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/ventas/{id}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('/ventas/{id}/ticket', [InvoiceController::class, 'ticket'])->name('invoices.ticket');

    // LOGOUT - Todos los usuarios autenticados
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // ============================================
    // MÓDULOS CRUD COMPLETO - SOLO PROPIETARIO
    // ============================================
    Route::middleware('role:Propietario')->group(function () {
        // Productos - Solo crear, editar y eliminar para propietario
        Route::get('productos/crear', [ProductoController::class, 'create'])->name('productos.create');
        Route::post('productos', [ProductoController::class, 'store'])->name('productos.store');
        Route::get('productos/{producto}/editar', [ProductoController::class, 'edit'])->name('productos.edit');
        Route::put('productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
        Route::delete('productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
        Route::delete('productos/{producto}/imagenes/{imagen}', [ProductoController::class, 'deleteImage'])->name('productos.deleteImage');
        Route::post('productos/{producto}/variantes', [ProductoVarianteController::class, 'store'])->name('productos.variantes.store');
        Route::put('productos/{producto}/variantes/{variante}', [ProductoVarianteController::class, 'update'])->name('productos.variantes.update');
        Route::delete('productos/{producto}/variantes/{variante}', [ProductoVarianteController::class, 'destroy'])->name('productos.variantes.destroy');

        // Categorías - Solo crear, editar y eliminar para propietario
        Route::get('categorias/crear', [CategoriaController::class, 'create'])->name('categorias.create');
        Route::get('categorias/{categoria}/editar', [CategoriaController::class, 'edit'])->name('categorias.edit');
        Route::post('categorias', [CategoriaController::class, 'store'])->name('categorias.store');
        Route::post('categorias/{categoria}/productos', [CategoriaController::class, 'addProducts'])->name('categorias.productos.store');
        Route::delete('categorias/{categoria}/productos/{producto}', [CategoriaController::class, 'removeProduct'])->name('categorias.productos.destroy');
        Route::put('categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

        // Promociones - Solo crear, editar y eliminar para propietario
        Route::get('promociones/crear', [PromocionController::class, 'create'])->name('promociones.create');
        Route::get('promociones/{promocion}/editar', [PromocionController::class, 'edit'])->name('promociones.edit');
        Route::post('promociones', [PromocionController::class, 'store'])->name('promociones.store');
        Route::put('promociones/{promocion}', [PromocionController::class, 'update'])->name('promociones.update');
        Route::delete('promociones/{promocion}', [PromocionController::class, 'destroy'])->name('promociones.destroy');

        // Proveedores - CRUD completo para propietario
        Route::get('proveedores', [\App\Http\Controllers\ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('proveedores/crear', [\App\Http\Controllers\ProveedorController::class, 'create'])->name('proveedores.create');
        Route::post('proveedores', [\App\Http\Controllers\ProveedorController::class, 'store'])->name('proveedores.store');
        Route::get('proveedores/{proveedor}/detalles', [\App\Http\Controllers\ProveedorController::class, 'show'])->name('proveedores.show');
        Route::get('proveedores/{proveedor}/editar', [\App\Http\Controllers\ProveedorController::class, 'edit'])->name('proveedores.edit');
        Route::put('proveedores/{proveedor}', [\App\Http\Controllers\ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('proveedores/{proveedor}', [\App\Http\Controllers\ProveedorController::class, 'destroy'])->name('proveedores.destroy');

        // Compras - Flujo completo con detalle
        Route::get('compras', [\App\Http\Controllers\CompraController::class, 'index'])->name('compras.index');
        Route::get('compras/crear', [\App\Http\Controllers\CompraController::class, 'create'])->name('compras.create');
        Route::post('compras', [\App\Http\Controllers\CompraController::class, 'store'])->name('compras.store');
        Route::get('compras/{compra}/detalles', [\App\Http\Controllers\CompraController::class, 'show'])->name('compras.show');
        Route::get('compras/{compra}/editar', [\App\Http\Controllers\CompraController::class, 'edit'])->name('compras.edit');
        Route::put('compras/{compra}', [\App\Http\Controllers\CompraController::class, 'update'])->name('compras.update');
        Route::delete('compras/{compra}', [\App\Http\Controllers\CompraController::class, 'destroy'])->name('compras.destroy');

        // Inventarios FIFO - movimientos y técnicas
        Route::resource('inventarios', \App\Http\Controllers\MovimientoInventarioController::class);
        Route::resource('tecnicas-inventario', \App\Http\Controllers\TecnicaInventarioController::class)
            ->parameters(['tecnicas-inventario' => 'tecnica_inventario']);

        // Usuarios (CRUD completo con URLs en español)
        Route::get('usuarios/crear', [UserController::class, 'create'])->name('usuarios.create');
        Route::get('usuarios/{usuario}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
        Route::get('usuarios/{usuario}/detalles', [UserController::class, 'show'])->name('usuarios.show');
        Route::post('usuarios/{usuario}/toggle-estado', [UserController::class, 'toggleEstado'])->name('usuarios.toggle-estado');
        Route::resource('usuarios', UserController::class)->except(['create', 'edit', 'show']);

        // Métodos de Pago (CRUD completo)
        Route::resource('metodos-pago', MetodoPagoController::class)
            ->parameters(['metodos-pago' => 'metodo_pago']);

        // Créditos (editar y eliminar)
        Route::get('/creditos/{id}/edit', [CreditoController::class, 'edit'])->name('creditos.edit');
        Route::put('/creditos/{id}', [CreditoController::class, 'update'])->name('creditos.update');
        Route::delete('/creditos/{id}', [CreditoController::class, 'destroy'])->name('creditos.destroy');

        // Pagos (editar y eliminar)
        Route::get('/pagos/{id}/edit', [PagoController::class, 'edit'])->name('pagos.edit');
        Route::put('/pagos/{id}', [PagoController::class, 'update'])->name('pagos.update');
        Route::delete('/pagos/{id}', [PagoController::class, 'destroy'])->name('pagos.destroy');
    });

    // Reportes - Solo Propietario y Vendedor
    Route::middleware('role:Propietario,Vendedor')->group(function () {
        Route::get('/reportes', [ReportController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/stats', [ReportController::class, 'stats'])->name('reportes.stats');
        Route::get('/reportes/{tipo}', [ReportController::class, 'show'])->name('reportes.show');
        Route::get('/reportes/{tipo}/pdf', [ReportController::class, 'pdf'])->name('reportes.pdf');
    });

    // Créditos - Propietario y Vendedor
    Route::middleware('role:Propietario,Vendedor')->group(function () {
        Route::get('/creditos', [CreditoController::class, 'index'])->name('creditos.index');
        Route::get('/creditos/{id}/detalles', [CreditoController::class, 'show'])->name('creditos.show');
        Route::post('/creditos/pago', [CreditoController::class, 'registrarPago'])->name('creditos.registrar-pago');
        Route::post('/creditos/actualizar-estados', [CreditoController::class, 'actualizarEstados'])->name('creditos.actualizar-estados');
        Route::get('/creditos/reporte/mora', [CreditoController::class, 'reporteMora'])->name('creditos.reporte-mora');
    });

    // Pagos - Propietario y Vendedor
    Route::middleware('role:Propietario,Vendedor')->group(function () {
        Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/{id}/detalles', [PagoController::class, 'show'])->name('pagos.show');
        Route::get('/pagos/registrar', [PagoController::class, 'create'])->name('pagos.create');
        Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
        Route::get('/pagos/buscar-cuotas', [PagoController::class, 'buscarCuotas'])->name('pagos.buscar-cuotas');
        Route::get('/pagos/historial/{cuotaId}', [PagoController::class, 'historialCuota'])->name('pagos.historial');
    });

    // Bitácora de Auditoría - Solo Propietario
    Route::middleware('role:Propietario')->group(function () {
        Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
        Route::get('/bitacora/{id}', [BitacoraController::class, 'show'])->name('bitacora.show');
        Route::get('/bitacora/exportar/csv', [BitacoraController::class, 'exportar'])->name('bitacora.exportar');
        Route::get('/api/bitacora/estadisticas', [BitacoraController::class, 'estadisticas'])->name('bitacora.estadisticas');
    });

    // Gestión de Pedidos - Propietario y Vendedor
    Route::middleware('role:Propietario,Vendedor')->group(function () {
        Route::get('/pedidos', [\App\Http\Controllers\GestionPedidosController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/crear', [\App\Http\Controllers\GestionPedidosController::class, 'create'])->name('pedidos.create');
        Route::post('/pedidos', [\App\Http\Controllers\GestionPedidosController::class, 'store'])->name('pedidos.admin.store');
        Route::get('/pedidos/{id}/detalles', [\App\Http\Controllers\GestionPedidosController::class, 'show'])->name('pedidos.show');
        Route::get('/pedidos/{id}/editar', [\App\Http\Controllers\GestionPedidosController::class, 'edit'])->name('pedidos.edit');
        Route::put('/pedidos/{id}', [\App\Http\Controllers\GestionPedidosController::class, 'update'])->name('pedidos.update');
        Route::patch('/pedidos/{id}/accion', [\App\Http\Controllers\GestionPedidosController::class, 'accion'])->name('pedidos.accion');
        Route::patch('/pedidos/{id}/marcar-enviado', [PedidoOnlineController::class, 'marcarComoEnviado'])->name('pedidos.marcar-enviado');
        Route::post('/pedidos/{id}/verificar-pago', [\App\Http\Controllers\GestionPedidosController::class, 'verificarPago'])->name('pedidos.verificar-pago');
    });

    // Mis Créditos y Pagos - Solo Cliente
    Route::middleware('role:Cliente')->group(function () {
        // Mis Créditos
        Route::get('/mis-creditos', [\App\Http\Controllers\MisCreditosController::class, 'index'])->name('mis-creditos.index');
        Route::get('/mis-creditos/{id}/detalles', [\App\Http\Controllers\MisCreditosController::class, 'show'])->name('mis-creditos.show');
        Route::post('/mis-creditos/pago', [\App\Http\Controllers\MisCreditosController::class, 'registrarPago'])->name('mis-creditos.registrar-pago');

        Route::post('/pagos/generar-qr', [PagoController::class, 'generarQR'])->name('pagos.generar-qr');
        Route::post('/pagos/generar-qr-cuota/{cuotaId}', [PagoCuotaController::class, 'generarQRCuota'])->name('pagos.generar-qr-cuota');
        Route::get('/pagos/verificar-estado/{pagoId}', [PagoCuotaController::class, 'verificarEstadoPago'])->name('pagos.verificar-estado-pago');
        
        // Mis Pedidos - Solo Cliente
        Route::get('/mis-pedidos', [MisPedidosController::class, 'index'])->name('mis-pedidos.index');
        Route::get('/mis-pedidos/{id}/detalles', [MisPedidosController::class, 'show'])->name('mis-pedidos.show');
        Route::post('/mis-pedidos/{id}/confirmar', [MisPedidosController::class, 'confirmarRecibido'])->name('mis-pedidos.confirmar-recepcion');
        
        // Mis Pagos -Solo Cliente
        Route::get('/mis-pagos', [\App\Http\Controllers\MisPagosController::class, 'index'])->name('mis-pagos.index');

        // Pedidos Online - Cliente crea pedido desde carrito
        Route::post('/carrito/realizar-pedido', [PedidoOnlineController::class, 'realizarPedido'])->name('carrito.realizar-pedido');
    });
});

// ============================================
// WEBHOOKS DE PAGOFACIL (SIMULADOS)
// Estas rutas NO requieren autenticación
// ============================================

Route::post('/webhook/pagofacil-simulado/venta', [PedidoOnlineController::class, 'webhookVentaSimulado'])->name('webhook.pagofacil.venta');
Route::post('/webhook/pagofacil-simulado/cuota', [PagoCuotaController::class, 'webhookCuotaSimulado'])->name('webhook.pagofacil.cuota');
Route::post('/pagofacil/callback', [PedidoOnlineController::class, 'pagofacilCallback'])->name('pagofacil.callback');

// ============================================
// ENDPOINTS DE PRUEBA (SOLO DESARROLLO)
// Eliminar en producción
// ============================================
Route::post('/pagofacil-simulado/confirmar-pago', [PedidoOnlineController::class, 'confirmarPagoSimulado'])->name('pagofacil.confirmar-simulado');
Route::post('/pagofacil-simulado/confirmar-pago-cuota', [PagoCuotaController::class, 'confirmarPagoSimulado'])->name('pagofacil.confirmar-cuota-simulado');

// Las rutas públicas de la tienda están centralizadas en routes/public.php
