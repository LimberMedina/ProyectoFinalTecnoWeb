<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\MetodoPago;
use App\Models\User;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * ProductoController - CRUD de Productos
 * 
 * Implementación MVC completa con:
 * - Policy para autorización dinámica desde BD
 * - Form Requests para validación
 * - Eager Loading para optimización
 * - Inertia.js para renderizar vistas Vue
 */
class ProductoController extends Controller
{
    /**
     * Constructor - el catálogo público no usa authorizeResource.
     * Las acciones de escritura quedan protegidas por middleware y/o rutas específicas.
     */
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     * 
     * Lista productos con paginación, búsqueda y filtro por categoría
     * Incluye eager loading de categoría para evitar N+1
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $categoriaId = $request->input('categoria', '');
        $highlight = $request->input('highlight', '');

        $rol = 'cliente';
        if ($request->user() && $request->user()->role) {
            $rol = $request->user()->role->nombre;
        }

        // Flujo específico de vendedor: listado por producto principal.
        if ($request->user() && $request->user()->esVendedor()) {
            $talla = $request->input('talla', '');
            $color = $request->input('color', '');

            $productos = Producto::with([
                    'categoria',
                    'variantes' => function ($query) {
                        $query->orderBy('id');
                    },
                ])
                ->when($search, function ($query, $search) {
                    $query->where(function ($productoQuery) use ($search) {
                        $productoQuery->where('nombre', 'ilike', "%{$search}%")
                            ->orWhere('codigo', 'ilike', "%{$search}%")
                            ->orWhereHas('variantes', function ($varianteQuery) use ($search) {
                                $varianteQuery->where('sku', 'ilike', "%{$search}%");
                            });
                    });
                })
                ->when($categoriaId, function ($query, $categoriaId) {
                    $query->where('categoria_id', $categoriaId);
                })
                ->when($talla, function ($query, $talla) {
                    $query->whereHas('variantes', function ($varianteQuery) use ($talla) {
                        $varianteQuery->where('talla', 'ilike', "%{$talla}%");
                    });
                })
                ->when($color, function ($query, $color) {
                    $query->whereHas('variantes', function ($varianteQuery) use ($color) {
                        $varianteQuery->where('color', 'ilike', "%{$color}%");
                    });
                })
                ->whereHas('variantes')
                ->orderBy('created_at', 'desc')
                ->paginate(12)
                ->withQueryString();

            $categorias = Categoria::select('id', 'nombre')
                ->orderBy('nombre')
                ->get();

            $clientes = User::with('role')
                ->whereHas('role', function ($query) {
                    $query->whereRaw('LOWER(nombre) = ?', ['cliente']);
                })
                ->where('estado', true)
                ->select('id', 'nombre', 'apellidos', 'email', 'ci')
                ->orderBy('nombre')
                ->limit(300)
                ->get();

            $metodosPago = MetodoPago::query()
                ->select('id', 'nombre')
                ->orderBy('nombre')
                ->get()
                ->map(function ($metodo) {
                    return [
                        'id' => $metodo->id,
                        'nombre' => $metodo->nombre,
                        'valor' => mb_strtolower(trim((string) $metodo->nombre)),
                    ];
                })
                ->values();

            $clienteFinal = User::whereRaw('LOWER(nombre) = ?', ['consumidor final'])
                ->where('apellidos', '')
                ->whereHas('role', function ($query) {
                    $query->whereRaw('LOWER(nombre) = ?', ['cliente']);
                })
                ->where('estado', true)
                ->first(['id']);

            return Inertia::render('Productos/IndexVendedor', [
                'productos' => $productos,
                'categorias' => $categorias,
                'clientes' => $clientes,
                'clienteFinalId' => $clienteFinal?->id,
                'metodosPago' => $metodosPago,
                'filters' => [
                    'search' => $search,
                    'categoria' => $categoriaId,
                    'talla' => $talla,
                    'color' => $color,
                    'highlight' => $highlight,
                ],
                'rol' => $rol,
            ]);
        }
        
        $productos = Producto::with(['categoria', 'variantes'])
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'ilike', "%{$search}%")
                      ->orWhere('codigo', 'ilike', "%{$search}%");
            })
            ->when($categoriaId, function ($query, $categoriaId) {
                $query->where('categoria_id', $categoriaId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Obtener todas las categorías para el filtro
        $categorias = Categoria::select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'categorias' => $categorias,
            'filters' => [
                'search' => $search,
                'categoria' => $categoriaId,
                'highlight' => $highlight,
            ],
            'rol' => $rol,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * 
     * Muestra formulario de creación con lista de categorías
     */
    public function create(Request $request)
    {
        $categorias = Categoria::select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Productos/Create', [
            'categorias' => $categorias,
            'categoriaId' => $request->input('categoria'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * Validación via StoreProductoRequest
     * Manejo de imagen (upload a storage)
     */
    public function store(StoreProductoRequest $request)
    {
        $data = $this->buildProductoData($request, null);

        Producto::create($data);

        return redirect()->route('productos.index', [], 303)
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     * 
     * Vista detalle de un producto
     */
    public function show(Request $request, $productoId)
    {
        $producto = Producto::with(['categoria', 'promociones', 'variantes'])->find($productoId);

        if (!$producto) {
            return redirect()->route('productos.index')->with('error', 'Producto no encontrado');
        }

        $rol = 'cliente';
        if ($request->user() && $request->user()->role) {
            $rol = $request->user()->role->nombre;
        }

        return Inertia::render('Productos/Show', [
            'producto' => $producto,
            'rol' => $rol,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * 
     * Formulario de edición con datos precargados
     */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        $producto->load(['variantes']);

        return Inertia::render('Productos/Edit', [
            'producto' => $producto,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * 
     * Validación via UpdateProductoRequest
     * Manejo de imagen (reemplaza anterior si existe nueva)
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $data = $this->buildProductoData($request, $producto);

        $producto->update($data);

        return redirect()->route('productos.index', [], 303)
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     * 
     * Elimina producto si no tiene ventas asociadas (validado en Policy)
     */
    public function destroy(Producto $producto)
    {
        if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
            Storage::disk('public')->delete($producto->imagen);
        }

        if ($producto->qr && Storage::disk('public')->exists($producto->qr)) {
            Storage::disk('public')->delete($producto->qr);
        }

        $producto->delete();

        return redirect()->route('productos.index', [], 303)
            ->with('success', 'Producto eliminado exitosamente.');
    }

    /**
     * Eliminar una imagen específica del producto
     */
    public function deleteImage(Producto $producto, $imagenId)
    {
        if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->update(['imagen' => null]);

        return back()->with('success', 'Imagen eliminada exitosamente.');
    }

    private function buildProductoData(Request $request, ?Producto $producto = null): array
    {
        $data = $request->validated();
        $baseVenta = isset($data['precio_venta_base']) && $data['precio_venta_base'] !== null
            ? $data['precio_venta_base']
            : ($producto?->precio_venta_base ?? $producto?->precio_venta ?? 0);
        $baseMayorista = isset($data['precio_venta_mayorista_base']) && $data['precio_venta_mayorista_base'] !== null
            ? $data['precio_venta_mayorista_base']
            : ($producto?->precio_venta_mayorista_base ?? $producto?->precio_venta_mayorista ?? $baseVenta);

        if ($request->hasFile('imagen')) {
            if ($producto && $producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }

            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        } elseif ($producto) {
            $data['imagen'] = $producto->imagen;
        } else {
            $data['imagen'] = null;
        }

        if ($request->hasFile('qr')) {
            if ($producto && $producto->qr && Storage::disk('public')->exists($producto->qr)) {
                Storage::disk('public')->delete($producto->qr);
            }

            $data['qr'] = $request->file('qr')->store('productos/qr', 'public');
        } elseif ($producto) {
            $data['qr'] = $producto->qr;
        } else {
            $data['qr'] = null;
        }

        $data['precio_venta_base'] = $baseVenta;
        $data['precio_venta_mayorista_base'] = $baseMayorista;

        return $data;
    }
}

