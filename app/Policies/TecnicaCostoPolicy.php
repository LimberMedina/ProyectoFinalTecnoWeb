<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\TecnicaCosto;
use App\Models\User;

class TecnicaCostoPolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'tecnicas-costo.index');
    }

    public function view(?User $user, TecnicaCosto $tecnicaCosto): bool
    {
        return $this->userHasAccessToRoute($user, 'tecnicas-costo.show');
    }

    public function create(User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'tecnicas-costo.create');
    }

    public function update(User $user, TecnicaCosto $tecnicaCosto): bool
    {
        return $this->userHasAccessToRoute($user, 'tecnicas-costo.edit');
    }

    public function delete(User $user, TecnicaCosto $tecnicaCosto): bool
    {
        return $this->userHasAccessToRoute($user, 'tecnicas-costo.destroy');
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
