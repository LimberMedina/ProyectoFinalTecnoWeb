<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\MovimientoInventario;
use App\Models\User;

class MovimientoInventarioPolicy
{
    public function viewAny(?User $user): bool
    {
        if ($user && (int) $user->role_id === 1) {
            return true;
        }

        return $this->userHasAccessToRoute($user, 'inventarios.index');
    }

    public function view(?User $user, MovimientoInventario $movimientoInventario): bool
    {
        if ($user && (int) $user->role_id === 1) {
            return true;
        }

        return $this->userHasAccessToRoute($user, 'inventarios.show');
    }

    public function create(User $user): bool
    {
        if ((int) $user->role_id === 1) {
            return true;
        }

        return $this->userHasAccessToRoute($user, 'inventarios.create');
    }

    public function update(User $user, MovimientoInventario $movimientoInventario): bool
    {
        if ((int) $user->role_id === 1) {
            return true;
        }

        return $this->userHasAccessToRoute($user, 'inventarios.edit');
    }

    public function delete(User $user, MovimientoInventario $movimientoInventario): bool
    {
        if ((int) $user->role_id === 1) {
            return true;
        }

        return $this->userHasAccessToRoute($user, 'inventarios.destroy');
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
