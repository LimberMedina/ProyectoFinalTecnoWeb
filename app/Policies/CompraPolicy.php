<?php

namespace App\Policies;

use App\Models\Compra;
use App\Models\MenuItem;
use App\Models\User;

class CompraPolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'compras.index');
    }

    public function view(?User $user, Compra $compra): bool
    {
        return $this->userHasAccessToRoute($user, 'compras.show');
    }

    public function create(User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'compras.create');
    }

    public function update(User $user, Compra $compra): bool
    {
        return $this->userHasAccessToRoute($user, 'compras.edit');
    }

    public function delete(User $user, Compra $compra): bool
    {
        return $this->userHasAccessToRoute($user, 'compras.destroy');
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
