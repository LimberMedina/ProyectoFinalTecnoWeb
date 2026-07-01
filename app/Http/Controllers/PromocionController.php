<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use App\Models\Producto;
use App\Models\Categoria;
use App\Http\Requests\StorePromocionRequest;
use App\Http\Requests\UpdatePromocionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromocionController extends Controller
{
    public function __construct()
    {
        // Aplicar autorización solo a métodos que la necesitan (create, store, edit, update, delete)
        $this->middleware('can:create,App\Models\Promocion')->only('create', 'store');
        $this->middleware('can:update,promocion')->only('edit', 'update');
        $this->middleware('can:delete,promocion')->only('destroy');
    }

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $user = $request->user();
        $isOwner = $user?->esPropietario() ?? false;
        
        $promociones = Promocion::with(['productos', 'categorias'])
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'ilike', "%{$search}%")
                      ->orWhere('descripcion', 'ilike', "%{$search}%");
            })
            // Si no es propietario, solo mostrar promociones activas y vigentes
            ->when(!$isOwner, function ($query) {
                $query->where('estado', true)
                      ->where('fecha_inicio', '<=', now())
                      ->where('fecha_fin', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $rol = 'cliente';
        if ($user && $user->role) {
            $rol = $user->role->nombre;
        }

        return Inertia::render('Promociones/Index', [
            'promociones' => $promociones,
            'filters' => ['search' => $search],
            'rol' => $rol,
        ]);
    }

    public function create()
    {
        
        $productos = Producto::select('id', 'nombre', 'codigo')->orderBy('nombre')->get();
        $categorias = Categoria::select('id', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Promociones/Create', [
            'productos' => $productos,
            'categorias' => $categorias,
        ]);
    }

    public function store(StorePromocionRequest $request)
    {
        $data = $request->validated();
        
        $productos = $data['productos'] ?? [];
        $categorias = $data['categorias'] ?? [];
        unset($data['productos'], $data['categorias']);

        $promocion = Promocion::create($data);

        // Asociar productos con pivot data
        if (!empty($productos)) {
            $productosPivot = [];
            foreach ($productos as $producto) {
                $productosPivot[$producto['id']] = [
                    'aplica_mayorista' => $producto['aplica_mayorista'] ?? false,
                    'aplica_minorista' => $producto['aplica_minorista'] ?? false,
                ];
            }
            $promocion->productos()->attach($productosPivot);
        }

        // Asociar categorías con pivot data
        if (!empty($categorias)) {
            $categoriasPivot = [];
            foreach ($categorias as $categoria) {
                $categoriasPivot[$categoria['id']] = [
                    'aplica_mayorista' => $categoria['aplica_mayorista'] ?? false,
                    'aplica_minorista' => $categoria['aplica_minorista'] ?? false,
                ];
            }
            $promocion->categorias()->attach($categoriasPivot);
        }

        return redirect()->route('promociones.index')
            ->with('success', 'Promoción creada exitosamente.');
    }

    public function show(Promocion $promocion)
    {
        $user = auth()->user();
        $isOwner = $user?->esPropietario() ?? false;

        // Si no es propietario, verificar que la promoción esté activa y vigente
        if (!$isOwner) {
            if (!$promocion->estado || 
                $promocion->fecha_inicio > now() || 
                $promocion->fecha_fin < now()) {
                abort(403, 'Esta promoción no está disponible en este momento.');
            }
        }

        $promocion->load([
            'productos' => function ($query) {
                $query->with([
                    'imagenes',
                    'categoria:id,nombre',
                    'variantes',
                ])->withPivot('aplica_mayorista', 'aplica_minorista');
            },
            'categorias' => function ($query) {
                $query->withPivot('aplica_mayorista', 'aplica_minorista');
            },
            'categorias.productos' => function ($query) {
                $query->with([
                    'imagenes',
                    'categoria:id,nombre',
                    'variantes',
                ]);
            },
        ]);

        return Inertia::render('Promociones/Show', [
            'promocion' => $promocion,
        ]);
    }

    public function edit(Promocion $promocion)
    {
        
        $promocion->load([
            'productos' => function ($query) {
                $query->withPivot('aplica_mayorista', 'aplica_minorista');
            },
            'categorias' => function ($query) {
                $query->withPivot('aplica_mayorista', 'aplica_minorista');
            }
        ]);
        
        $productos = Producto::select('id', 'nombre', 'codigo')->orderBy('nombre')->get();
        $categorias = Categoria::select('id', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Promociones/Edit', [
            'promocion' => $promocion,
            'productos' => $productos,
            'categorias' => $categorias,
        ]);
    }

    public function update(UpdatePromocionRequest $request, Promocion $promocion)
    {
        
        $data = $request->validated();
        
        $productos = $data['productos'] ?? [];
        $categorias = $data['categorias'] ?? [];
        unset($data['productos'], $data['categorias']);

        $promocion->update($data);

        // Sincronizar productos con pivot data
        $productosPivot = [];
        foreach ($productos as $producto) {
            $productosPivot[$producto['id']] = [
                'aplica_mayorista' => $producto['aplica_mayorista'] ?? false,
                'aplica_minorista' => $producto['aplica_minorista'] ?? false,
            ];
        }
        $promocion->productos()->sync($productosPivot);

        // Sincronizar categorías con pivot data
        $categoriasPivot = [];
        foreach ($categorias as $categoria) {
            $categoriasPivot[$categoria['id']] = [
                'aplica_mayorista' => $categoria['aplica_mayorista'] ?? false,
                'aplica_minorista' => $categoria['aplica_minorista'] ?? false,
            ];
        }
        $promocion->categorias()->sync($categoriasPivot);

        return redirect()->route('promociones.index')
            ->with('success', 'Promoción actualizada exitosamente.');
    }

    public function destroy(Promocion $promocion)
    {
        
        // Desasociar relaciones antes de eliminar
        $promocion->productos()->detach();
        $promocion->categorias()->detach();
        
        $promocion->delete();

        return redirect()->route('promociones.index')
            ->with('success', 'Promoción eliminada exitosamente.');
    }
}

