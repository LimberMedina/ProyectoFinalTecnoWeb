<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MetodoPagoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $metodosPago = MetodoPago::query()
            ->withCount(['ventas', 'pagos'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('MetodosPago/Index', [
            'metodosPago' => $metodosPago,
            'filters' => [
                'search' => $search,
            ],
            'estadisticas' => [
                'total_metodos' => MetodoPago::count(),
                'con_descripcion' => MetodoPago::whereNotNull('descripcion')
                    ->where('descripcion', '!=', '')
                    ->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('MetodosPago/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:metodos_pago,nombre'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['descripcion'] = filled($data['descripcion'] ?? null)
            ? $data['descripcion']
            : null;

        MetodoPago::create($data);

        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago creado exitosamente.');
    }

    public function show(MetodoPago $metodoPago)
    {
        abort(404);
    }

    public function edit(MetodoPago $metodoPago)
    {
        return Inertia::render('MetodosPago/Edit', [
            'metodoPago' => $metodoPago,
        ]);
    }

    public function update(Request $request, MetodoPago $metodoPago)
    {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('metodos_pago', 'nombre')->ignore($metodoPago->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['descripcion'] = filled($data['descripcion'] ?? null)
            ? $data['descripcion']
            : null;

        $metodoPago->update($data);

        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago actualizado exitosamente.');
    }

    public function destroy(MetodoPago $metodoPago)
    {
        $metodoPago->delete();

        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago eliminado exitosamente.');
    }
}
