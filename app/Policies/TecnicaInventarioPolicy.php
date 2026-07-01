<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\TecnicaInventario;
use App\Models\User;

class TecnicaInventarioPolicy
{
    public function viewAny(?User $user): bool
    {
        if ($user && (int) $user->role_id === 1) {
            return true;
        }

        return $this->userHasAccessToRoute($user, 'tecnicas-inventario.index');
    }

    public function view(?User $user, TecnicaInventario $tecnicaInventario): bool
    {
        if ($user && (int) $user->role_id === 1) {
            return true;
        }

        return $this->userHasAccessToRoute($user, 'tecnicas-inventario.show');
    }

    public function create(User $user): bool
    {
        if ((int) $user->role_id === 1) {
            return true;
        }

        return $this->userHasAccessToRoute($user, 'tecnicas-inventario.create');
    }

    public function update(User $user, TecnicaInventario $tecnicaInventario): bool
    {
        if ((int) $user->role_id === 1) {
            return true;
        }

        return $this->userHasAccessToRoute($user, 'tecnicas-inventario.edit');
    }

    public function delete(User $user, TecnicaInventario $tecnicaInventario): bool
    {
        if ((int) $user->role_id === 1) {
            return true;
        }

        return $this->userHasAccessToRoute($user, 'tecnicas-inventario.destroy');
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
