<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ProveedorPolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'proveedores.index');
    }

    public function view(?User $user, Proveedor $proveedor): bool
    {
        return $this->userHasAccessToRoute($user, 'proveedores.show');
    }

    public function create(User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'proveedores.create');
    }

    public function update(User $user, Proveedor $proveedor): bool
    {
        return $this->userHasAccessToRoute($user, 'proveedores.edit');
    }

    public function delete(User $user, Proveedor $proveedor): bool
    {
        $hasDeletePermission = $this->userHasAccessToRoute($user, 'proveedores.destroy');
        $hasCompras = Schema::hasTable('compra')
            ? DB::table('compra')->where('id_proveedor', $proveedor->id)->exists()
            : false;

        return $hasDeletePermission && ! $hasCompras;
    }

    private function userHasAccessToRoute(?User $user, string $route): bool
    {
        if (! $user || ! $user->role_id) {
            return false;
        }

        return MenuItem::where('role_id', $user->role_id)
            ->where('ruta_laravel', $route)
            ->exists();
    }
}