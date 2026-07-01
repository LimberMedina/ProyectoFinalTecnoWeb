<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PromocionController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Rutas públicas sin autenticación
|
*/

// Productos públicos - Listado y detalles públicos para todos
Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
// Asegurar que {producto} sea numérico para que rutas como /productos/crear no colisionen
Route::get('/productos/{producto}', [ProductoController::class, 'show'])
	->whereNumber('producto')
	->name('productos.show');
Route::get('/tienda/productos', [ProductoController::class, 'index'])->name('tienda.productos');
// Asegurar que {productoId} sea numérico
Route::get('/tienda/producto/{productoId}', [ProductoController::class, 'show'])
	->whereNumber('productoId')
	->name('tienda.producto');

// Categorías públicas - Listado y detalles públicos para todos
Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
// Evitar que segmentos como 'crear' colisionen con la ruta show
Route::get('/categorias/{categoria}', [CategoriaController::class, 'show'])
	->whereNumber('categoria')
	->name('categorias.show');

// Promociones públicas - Listado y detalles públicos para todos
Route::get('/promociones', [PromocionController::class, 'index'])->name('promociones.index');
// Evitar que segmentos como 'crear' colisionen con la ruta show
Route::get('/promociones/{promocion}', [PromocionController::class, 'show'])
	->whereNumber('promocion')
	->name('promociones.show');
Route::get('/tienda/promociones', [PromocionController::class, 'index'])->name('tienda.promociones');
// Asegurar que {promocion} sea numérico para Route Model Binding
Route::get('/tienda/promocion/{promocion}', [PromocionController::class, 'show'])
	->whereNumber('promocion')
	->name('tienda.promocion');
