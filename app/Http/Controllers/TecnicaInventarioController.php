<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTecnicaInventarioRequest;
use App\Http\Requests\UpdateTecnicaInventarioRequest;
use App\Models\TecnicaInventario;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TecnicaInventarioController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(TecnicaInventario::class, 'tecnica_inventario');
    }

    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $tecnicas = TecnicaInventario::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('nombre', 'ilike', "%{$search}%")
                        ->orWhere('descripcion', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('TecnicasInventario/Index', [
            'tecnicas' => $tecnicas,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('TecnicasInventario/Create');
    }

    public function store(StoreTecnicaInventarioRequest $request)
    {
        TecnicaInventario::create($request->validated());

        return redirect()->route('tecnicas-inventario.index')
            ->with('success', 'Técnica de inventario creada exitosamente.');
    }

    public function show(TecnicaInventario $tecnica_inventario)
    {
        $tecnica_inventario->loadCount('movimientos');

        return Inertia::render('TecnicasInventario/Show', [
            'tecnica' => $tecnica_inventario,
        ]);
    }

    public function edit(TecnicaInventario $tecnica_inventario)
    {
        return Inertia::render('TecnicasInventario/Edit', [
            'tecnica' => $tecnica_inventario,
        ]);
    }

    public function update(UpdateTecnicaInventarioRequest $request, TecnicaInventario $tecnica_inventario)
    {
        $tecnica_inventario->update($request->validated());

        return redirect()->route('tecnicas-inventario.index')
            ->with('success', 'Técnica de inventario actualizada exitosamente.');
    }

    public function destroy(TecnicaInventario $tecnica_inventario)
    {
        if ($tecnica_inventario->movimientos()->exists()) {
            return redirect()->route('tecnicas-inventario.index')
                ->with('warning', 'No se puede eliminar la técnica porque tiene movimientos asociados.');
        }

        $tecnica_inventario->delete();

        return redirect()->route('tecnicas-inventario.index')
            ->with('success', 'Técnica de inventario eliminada exitosamente.');
    }
}
