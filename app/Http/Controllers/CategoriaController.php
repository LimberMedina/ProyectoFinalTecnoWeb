<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Categoria::class, 'categoria');
    }

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $highlight = $request->input('highlight', '');
        
        $categorias = Categoria::withCount('productos')
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'ilike', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $rol = 'cliente';
        if ($request->user() && $request->user()->role) {
            $rol = $request->user()->role->nombre;
        }

        return Inertia::render('Categorias/Index', [
            'categorias' => $categorias,
            'filters' => ['search' => $search, 'highlight' => $highlight],
            'rol' => $rol,
        ]);
    }

    public function create(Request $request)
    {
        // Pasar un flag para indicar si la vista se solicita dentro de un modal
        return Inertia::render('Categorias/Create', [
            'inModal' => $request->boolean('modal', false),
        ]);
    }

    public function store(StoreCategoriaRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('categorias', 'public');
        }

        $categoria = Categoria::create($data);

        // Si viene desde modal de productos, retornar JSON
        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return back()->with([
                'success' => 'Categoría creada exitosamente.',
                'categoria' => $categoria,
            ]);
        }

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    public function show(Request $request, Categoria $categoria)
    {
        $categoria->loadCount('productos');
        
        // Cargar productos de la categoría con paginación
        $productos = $categoria->productos()
            ->with(['imagenes', 'variantes'])
            ->orderBy('nombre')
            ->paginate(12);

        $productosCatalogo = Producto::with([
            'categoria:id,nombre',
            'imagenes',
            'variantes',
        ])
            ->select('id', 'codigo', 'nombre', 'imagen', 'precio_venta_base', 'precio_venta_mayorista_base', 'marca', 'categoria_id', 'descripcion', 'genero', 'estado')
            ->orderBy('nombre')
            ->get();

        $rol = 'cliente';
        if ($request->user() && $request->user()->role) {
            $rol = $request->user()->role->nombre;
        }

        return Inertia::render('Categorias/Show', [
            'categoria' => $categoria,
            'productos' => $productos,
            'productosCatalogo' => $productosCatalogo,
            'rol' => $rol,
        ]);
    }

    public function addProducts(Request $request, Categoria $categoria)
    {
        $this->authorize('update', $categoria);

        $validated = $request->validate([
            'producto_ids' => ['required', 'array', 'min:1'],
            'producto_ids.*' => ['integer', 'exists:productos,id'],
        ]);

        $productoIds = collect($validated['producto_ids'])->unique()->values();

        $actualizados = Producto::whereIn('id', $productoIds)
            ->whereNull('categoria_id')
            ->update(['categoria_id' => $categoria->id]);

        return back()->with('success', "{$actualizados} producto(s) agregados a la categoría.");
    }

    public function removeProduct(Categoria $categoria, Producto $producto)
    {
        $this->authorize('update', $categoria);

        abort_unless($producto->categoria_id === $categoria->id, 404);

        $producto->update([
            'categoria_id' => null,
        ]);

        return back()->with('success', 'Producto quitado de la categoría.');
    }

    public function edit(Categoria $categoria)
    {
        return Inertia::render('Categorias/Edit', [
            'categoria' => $categoria,
        ]);
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            if ($categoria->imagen && Storage::disk('public')->exists($categoria->imagen)) {
                Storage::disk('public')->delete($categoria->imagen);
            }

            $data['imagen'] = $request->file('imagen')->store('categorias', 'public');
        }

        $categoria->update($data);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->imagen && Storage::disk('public')->exists($categoria->imagen)) {
            Storage::disk('public')->delete($categoria->imagen);
        }

        // Verificar si tiene productos asociados
        $productosCount = $categoria->productos()->count();
        
        if ($productosCount > 0) {
            // Al eliminar la categoría, los productos quedarán sin categoría (categoria_id = null)
            $categoria->delete();
            
            return redirect()->route('categorias.index')
                ->with('warning', "Categoría eliminada. {$productosCount} producto(s) quedaron sin categoría asignada.");
        }
        
        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }
}

