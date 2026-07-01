<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * MenuService - Capa de Lógica de Negocio
 * 
 * Implementa el patrón de Menú Dinámico 3 Capas aprendido en clase:
 * - Persona → Grupo (ahora: User → Role)
 * - Grupo → Módulos (ahora: Role → MenuItems)
 * - Módulo → Acciones (ahora: MenuItem → Rutas Laravel)
 * 
 * Mejoras Laravel:
 * - Caché de menú por rol (5 minutos) para optimización
 * - Eager loading de relaciones para evitar N+1
 * - Mapeo de datos para Vue/Inertia
 */
class MenuService
{
    /**
     * Obtener menú dinámico para un usuario basado en sus roles
     * 
     * @param User $user
     * @return array
     */
    public function getMenuForUser(User $user): array
    {
        // Obtener role_id directo del usuario
        $roleId = $user->role_id;
        
        // Si no tiene role_id asignado, no mostrar menú
        if (!$roleId) {
            return [];
        }
        
        $roleIds = [$roleId];

        // Cache key único por combinación de roles
        $cacheKey = 'menu_items_v2_' . md5(implode('_', $roleIds));

        // Cachear menú por 5 minutos
        $menuItems = Cache::remember($cacheKey, 300, function () use ($roleIds) {
            // Obtener todos los items del menú (padres e hijos)
            // Filtrar items con orden < 999 (items visibles en menú)
            // Items con orden = 999 son solo para autorización (no visibles en sidebar)
            $allItems = MenuItem::with('children')
                ->whereIn('role_id', $roleIds)
                ->where('orden', '<', 999) // Solo items visibles en menú
                ->orderBy('orden')
                ->get(['id', 'etiqueta', 'ruta_laravel', 'icono', 'orden', 'role_id', 'parent_id']);

            $parentItems = $allItems->whereNull('parent_id');

            return $parentItems->map(function ($item) use ($allItems) {
                // Obtener hijos del item actual (ya filtrados por orden < 999)
                $children = $allItems->where('parent_id', $item->id)
                    ->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'label' => $child->etiqueta,
                            'route' => $child->ruta_laravel,
                            'icon' => $child->icono,
                            'order' => $child->orden,
                        ];
                    })
                    ->sortBy('order')
                    ->values()
                    ->toArray();

                return [
                    'id' => $item->id,
                    'label' => $item->etiqueta,
                    'route' => $item->ruta_laravel,
                    'icon' => $item->icono,
                    'order' => $item->orden,
                    'children' => $children,
                ];
            })
            ->sortBy('order')
            ->values()
            ->toArray();
        });

        if ((int) $roleId === 1) {
            $menuItems = collect($menuItems)
                ->map(function (array $item) {
                    if (($item['route'] ?? null) === 'inventarios.index') {
                        $item['label'] = 'Inventario';
                        $item['icon'] = 'FaWarehouse';
                        $item['order'] = 57;
                    }

                    if (($item['route'] ?? null) === 'metodos-pago.index') {
                        $item['label'] = 'Métodos de Pago';
                        $item['icon'] = 'FaWallet';
                        $item['order'] = 88;
                    }

                    return $item;
                })
                ->reject(function (array $item) {
                    return ($item['route'] ?? null) === 'tecnicas-inventario.index';
                })
                ->values()
                ->toArray();

            $hasInventory = collect($menuItems)->contains(function ($item) {
                return ($item['route'] ?? null) === 'inventarios.index';
            });

            if (! $hasInventory) {
                $menuItems[] = [
                    'id' => 'fallback-inventario',
                    'label' => 'Inventario',
                    'route' => 'inventarios.index',
                    'icon' => 'FaWarehouse',
                    'order' => 57,
                    'children' => [],
                ];

                $menuItems = collect($menuItems)
                    ->values()
                    ->toArray();
            }

            $hasMetodosPago = collect($menuItems)->contains(function ($item) {
                return ($item['route'] ?? null) === 'metodos-pago.index';
            });

            if (! $hasMetodosPago) {
                $menuItems[] = [
                    'id' => 'fallback-metodos-pago',
                    'label' => 'Métodos de Pago',
                    'route' => 'metodos-pago.index',
                    'icon' => 'FaWallet',
                    'order' => 88,
                    'children' => [],
                ];

                $menuItems = collect($menuItems)
                    ->values()
                    ->toArray();
            }

            $menuItems = $this->sortOwnerMenuItems($menuItems);
        }

        return $menuItems;
    }

    /**
     * Ordenar el sidebar del propietario por grupos funcionales.
     *
     * @param array $menuItems
     * @return array
     */
    private function sortOwnerMenuItems(array $menuItems): array
    {
        $orderMap = [
            'dashboard' => 10,
            'usuarios.index' => 20,
            'categorias.index' => 30,
            'productos.index' => 40,
            'proveedores.index' => 50,
            'compras.index' => 60,
            'inventarios.index' => 70,
            'promociones.index' => 80,
            'pedidos.index' => 90,
            'ventas.index' => 100,
            'metodos-pago.index' => 110,
            'pagos.index' => 120,
            'creditos.index' => 130,
            'reportes.index' => 9999,
        ];

        return collect($menuItems)
            ->sortBy(function (array $item) use ($orderMap) {
                return $orderMap[$item['route'] ?? ''] ?? 5000;
            })
            ->map(function (array $item) use ($orderMap) {
                if (!empty($item['children']) && is_array($item['children'])) {
                    $item['children'] = collect($item['children'])
                        ->sortBy(function (array $child) use ($orderMap) {
                            return $orderMap[$child['route'] ?? ''] ?? 5000;
                        })
                        ->values()
                        ->toArray();
                }

                return $item;
            })
            ->values()
            ->toArray();
    }

    /**
     * Limpiar caché de menús (útil al crear/modificar MenuItems)
     */
    public function clearMenuCache(): void
    {
        Cache::flush(); // O usar tags si Redis está configurado
    }

    /**
     * Verificar si un usuario tiene acceso a una ruta específica
     * 
     * @param User $user
     * @param string $route
     * @return bool
     */
    public function userHasAccessToRoute(User $user, string $route): bool
    {
        $roleId = $user->role_id;
        
        if (!$roleId) {
            return false;
        }

        return MenuItem::where('role_id', $roleId)
            ->where('ruta_laravel', $route)
            ->exists();
    }

    /**
     * Obtener todos los menús agrupados por rol
     * (útil para administración)
     * 
     * @return array
     */
    public function getMenusByRole(): array
    {
        return MenuItem::with('role')
            ->orderBy('role_id')
            ->orderBy('orden')
            ->get()
            ->groupBy('role.nombre')
            ->toArray();
    }
}
