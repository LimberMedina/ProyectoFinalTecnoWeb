<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Proveedor::class, 'proveedor');
    }

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $highlight = $request->input('highlight', '');
        $hasCompraTable = Schema::hasTable('compra');

        $proveedores = Proveedor::query()
            ->select('proveedor.*')
            ->when($hasCompraTable, function ($query) {
                $query->selectSub(
                    DB::table('compra')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('compra.id_proveedor', 'proveedor.id'),
                    'compras_count',
                );
            }, function ($query) {
                $query->selectRaw('0 as compras_count');
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('nombre', 'ilike', "%{$search}%")
                        ->orWhere('nit', 'ilike', "%{$search}%")
                        ->orWhere('telefono', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
            'filters' => ['search' => $search, 'highlight' => $highlight],
        ]);
    }

    public function create()
    {
        return Inertia::render('Proveedores/Create');
    }

    public function store(StoreProveedorRequest $request)
    {
        $data = $request->validated();
        $data['estado'] = $data['estado'] ?? 'Activo';

        Proveedor::create($data);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    public function show(Proveedor $proveedor)
    {
        $proveedor->setAttribute('compras_count', $this->getComprasCount($proveedor->id));

        return Inertia::render('Proveedores/Show', [
            'proveedor' => $proveedor,
        ]);
    }

    public function edit(Proveedor $proveedor)
    {
        return Inertia::render('Proveedores/Edit', [
            'proveedor' => $proveedor,
        ]);
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor)
    {
        $data = $request->validated();
        $data['estado'] = $data['estado'] ?? 'Activo';

        $proveedor->update($data);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $comprasCount = $this->getComprasCount($proveedor->id);

        if ($comprasCount > 0) {
            return redirect()->route('proveedores.index')
                ->with('warning', "No se puede eliminar el proveedor porque tiene {$comprasCount} compra(s) asociada(s).");
        }

        $proveedor->delete();

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado exitosamente.');
    }

    private function getComprasCount(int $proveedorId): int
    {
        if (! Schema::hasTable('compra')) {
            return 0;
        }

        return DB::table('compra')->where('id_proveedor', $proveedorId)->count();
    }
}