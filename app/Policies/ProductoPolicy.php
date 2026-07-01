<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;
use App\Models\MenuItem;


class ProductoPolicy
{
    /**
     * Ver lista de productos (Index)
     */
    public function viewAny(?User $user): bool
    {
        // Permitir a cualquiera (autenticado o no) listar productos
        return true;
    }

    /**
     * Ver un producto específico
     */
    public function view(?User $user, Producto $producto): bool
    {
        // Permitir a cualquiera (autenticado o no) ver detalles de productos
        return true;
    }

    /**
     * Crear un nuevo producto
     */
    public function create(User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'productos.create');
    }

    /**
     * Editar un producto existente
     */
    public function update(User $user, Producto $producto): bool
    {
        return $this->userHasAccessToRoute($user, 'productos.edit');
    }

    /**
     * Eliminar un producto
     */
    public function delete(User $user, Producto $producto): bool
    {
        // Verificar que tenga permiso de eliminar
        $hasDeletePermission = $this->userHasAccessToRoute($user, 'productos.destroy');
        
        // Validación adicional: no eliminar si tiene ventas asociadas
        $hasVentas = $producto->detallesVenta()->count() > 0;
        
        return $hasDeletePermission && !$hasVentas;
    }

    /**
     * Verificar si el usuario tiene acceso a una ruta específica
     * consultando menu_items en la BD
     */
    private function userHasAccessToRoute(User $user, string $route): bool
    {
        $roleId = $user->role_id;
        
        if (!$roleId) {
            return false;
        }

        // Buscar en menu_items si el rol del usuario tiene acceso a esta ruta
        return MenuItem::where('role_id', $roleId)
            ->where('ruta_laravel', $route)
            ->exists();
    }
}
