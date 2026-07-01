<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTecnicaCostoRequest;
use App\Http\Requests\UpdateTecnicaCostoRequest;
use App\Models\TecnicaCosto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TecnicaCostoController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(TecnicaCosto::class, 'tecnica_costo');
    }

    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $tecnicas = TecnicaCosto::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('nombre', 'ilike', "%{$search}%")
                        ->orWhere('descripcion', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('TecnicasCosto/Index', [
            'tecnicas' => $tecnicas,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('TecnicasCosto/Create');
    }

    public function store(StoreTecnicaCostoRequest $request)
    {
        TecnicaCosto::create($request->validated());

        return redirect()->route('tecnicas-costo.index')
            ->with('success', 'Técnica de costo creada exitosamente.');
    }

    public function show(TecnicaCosto $tecnica_costo)
    {
        $tecnica_costo->loadCount('movimientos');

        return Inertia::render('TecnicasCosto/Show', [
            'tecnica' => $tecnica_costo,
        ]);
    }

    public function edit(TecnicaCosto $tecnica_costo)
    {
        return Inertia::render('TecnicasCosto/Edit', [
            'tecnica' => $tecnica_costo,
        ]);
    }

    public function update(UpdateTecnicaCostoRequest $request, TecnicaCosto $tecnica_costo)
    {
        $tecnica_costo->update($request->validated());

        return redirect()->route('tecnicas-costo.index')
            ->with('success', 'Técnica de costo actualizada exitosamente.');
    }

    public function destroy(TecnicaCosto $tecnica_costo)
    {
        if ($tecnica_costo->movimientos()->exists()) {
            return redirect()->route('tecnicas-costo.index')
                ->with('warning', 'No se puede eliminar la técnica porque tiene movimientos asociados.');
        }

        $tecnica_costo->delete();

        return redirect()->route('tecnicas-costo.index')
            ->with('success', 'Técnica de costo eliminada exitosamente.');
    }
}
