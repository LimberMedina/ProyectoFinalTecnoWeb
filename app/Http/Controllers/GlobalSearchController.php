<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => ['nullable', 'string', 'max' => 100],
            ]);

            $term = trim((string) ($validated['q'] ?? ''));

            if (mb_strlen($term) < 2) {
                return response()->json([
                    'query' => $term,
                    'results' => [],
                ]);
            }

            $results = collect()
                ->merge($this->searchSections($term, $request->user() !== null))
                ->merge($this->searchTable(
                    table: 'productos',
                    idCandidates: ['id', 'producto_id', 'id_producto'],
                    titleCandidates: ['nombre', 'nombre_producto', 'titulo'],
                    descriptionCandidates: ['descripcion', 'detalle', 'marca'],
                    searchCandidates: ['nombre', 'nombre_producto', 'titulo', 'descripcion', 'detalle', 'codigo', 'marca'],
                    term: $term,
                    type: 'Producto',
                    icon: 'bi-box-seam',
                    urlResolver: fn ($id, $title) => $this->resolveDetailRoute(
                        ['productos.show', 'public.productos.show', 'producto.show'],
                        $id,
                        url('/productos') . '?buscar=' . rawurlencode($title)
                    ),
                    limit: 6,
                ))
                ->merge($this->searchTable(
                    table: 'categorias',
                    idCandidates: ['id', 'categoria_id', 'id_categoria'],
                    titleCandidates: ['nombre', 'nombre_categoria', 'titulo'],
                    descriptionCandidates: ['descripcion', 'detalle'],
                    searchCandidates: ['nombre', 'nombre_categoria', 'titulo', 'descripcion', 'detalle'],
                    term: $term,
                    type: 'Categoría',
                    icon: 'bi-grid',
                    urlResolver: fn ($id, $title) => $this->resolveDetailRoute(
                        ['categorias.show', 'public.categorias.show', 'categoria.show'],
                        $id,
                        url('/categorias') . '?buscar=' . rawurlencode($title)
                    ),
                    limit: 4,
                ))
                ->merge($this->searchTable(
                    table: 'promociones',
                    idCandidates: ['id', 'promocion_id', 'id_promocion'],
                    titleCandidates: ['nombre', 'titulo', 'nombre_promocion'],
                    descriptionCandidates: ['descripcion', 'detalle'],
                    searchCandidates: ['nombre', 'titulo', 'nombre_promocion', 'descripcion', 'detalle'],
                    term: $term,
                    type: 'Promoción',
                    icon: 'bi-tags',
                    urlResolver: fn ($id, $title) => $this->resolveDetailRoute(
                        ['promociones.show', 'public.promociones.show', 'promocion.show'],
                        $id,
                        url('/promociones') . '?buscar=' . rawurlencode($title)
                    ),
                    limit: 4,
                ))
                ->merge($this->searchTable(
                    table: 'ofertas',
                    idCandidates: ['id', 'oferta_id', 'id_oferta'],
                    titleCandidates: ['nombre', 'titulo', 'nombre_oferta'],
                    descriptionCandidates: ['descripcion', 'detalle'],
                    searchCandidates: ['nombre', 'titulo', 'nombre_oferta', 'descripcion', 'detalle'],
                    term: $term,
                    type: 'Oferta',
                    icon: 'bi-percent',
                    urlResolver: fn ($id, $title) => url('/promociones') . '?buscar=' . rawurlencode($title),
                    limit: 4,
                ));

            $normalizedTerm = $this->normalize($term);

            $results = $results
                ->unique(fn (array $result) => $result['type'] . '|' . $result['title'] . '|' . $result['url'])
                ->sortBy(function (array $result) use ($normalizedTerm) {
                    $title = $this->normalize($result['title']);

                    if ($title === $normalizedTerm) {
                        return 0;
                    }

                    if (Str::startsWith($title, $normalizedTerm)) {
                        return 1;
                    }

                    return 2;
                })
                ->take(15)
                ->values();

            return response()->json([
                'query' => $term,
                'results' => $results,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'query' => trim((string) $request->input('q', '')),
                'results' => [],
            ]);
        }
    }

    private function searchTable(
        string $table,
        array $idCandidates,
        array $titleCandidates,
        array $descriptionCandidates,
        array $searchCandidates,
        string $term,
        string $type,
        string $icon,
        callable $urlResolver,
        int $limit = 5
    ): Collection {
        try {
            if (!Schema::hasTable($table)) {
                return collect();
            }

            $columns = Schema::getColumnListing($table);
            $idColumn = $this->firstExistingColumn($columns, $idCandidates);
            $titleColumn = $this->firstExistingColumn($columns, $titleCandidates);
            $descriptionColumn = $this->firstExistingColumn($columns, $descriptionCandidates);

            $availableSearchColumns = array_values(array_filter(
                $searchCandidates,
                fn (string $column) => in_array($column, $columns, true)
            ));

            if (! $titleColumn || empty($availableSearchColumns)) {
                return collect();
            }

            $selectedColumns = array_values(array_unique(array_filter([
                $idColumn,
                $titleColumn,
                $descriptionColumn,
            ])));

            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

            $query = DB::table($table)
                ->select($selectedColumns)
                ->where(function ($subQuery) use ($availableSearchColumns, $operator, $term) {
                    foreach ($availableSearchColumns as $index => $column) {
                        if ($operator === 'ILIKE') {
                            if ($index === 0) {
                                $subQuery->where($column, $operator, "%{$term}%");
                            } else {
                                $subQuery->orWhere($column, $operator, "%{$term}%");
                            }
                        } else {
                            $like = Str::lower("%{$term}%");
                            $expr = "LOWER(COALESCE({$column}, '')) LIKE ?";

                            if ($index === 0) {
                                $subQuery->whereRaw($expr, [$like]);
                            } else {
                                $subQuery->orWhereRaw($expr, [$like]);
                            }
                        }
                    }
                });

            if (in_array('deleted_at', $columns, true)) {
                $query->whereNull('deleted_at');
            }

            if (in_array('activo', $columns, true)) {
                $query->where('activo', true);
            }

            return $query
                ->limit($limit)
                ->get()
                ->map(function ($row) use ($idColumn, $titleColumn, $descriptionColumn, $type, $icon, $urlResolver) {
                    $id = $idColumn ? data_get($row, $idColumn) : null;
                    $title = trim(strip_tags((string) data_get($row, $titleColumn, '')));

                    if ($title === '') {
                        return null;
                    }

                    $description = $descriptionColumn
                        ? trim(strip_tags((string) data_get($row, $descriptionColumn, '')))
                        : '';

                    return [
                        'id' => $id,
                        'type' => $type,
                        'title' => $title,
                        'description' => $description !== ''
                            ? Str::limit($description, 110)
                            : "Información de {$type} disponible en Tienda Elena.",
                        'url' => $urlResolver($id, $title, $row),
                        'icon' => $icon,
                    ];
                })
                ->filter()
                ->values();
        } catch (Throwable $exception) {
            report($exception);

            return collect();
        }
    }

    private function searchSections(string $term, bool $authenticated): Collection
    {
        $sections = [
            [
                'id' => 'inicio',
                'type' => 'Sección',
                'title' => 'Inicio',
                'description' => 'Página principal de Tienda Elena.',
                'keywords' => 'inicio principal tienda elena novedades',
                'url' => url('/'),
                'icon' => 'bi-house',
            ],
            [
                'id' => 'productos',
                'type' => 'Sección',
                'title' => 'Productos',
                'description' => 'Consulta los productos disponibles de la tienda.',
                'keywords' => 'productos catálogo comprar artículos precios',
                'url' => url('/productos'),
                'icon' => 'bi-bag',
            ],
            [
                'id' => 'categorias',
                'type' => 'Sección',
                'title' => 'Categorías',
                'description' => 'Explora los productos por categoría.',
                'keywords' => 'categorías tipos clasificación productos',
                'url' => url('/categorias'),
                'icon' => 'bi-grid',
            ],
            [
                'id' => 'promociones',
                'type' => 'Sección',
                'title' => 'Promociones',
                'description' => 'Ofertas y descuentos disponibles.',
                'keywords' => 'promociones ofertas descuentos rebajas',
                'url' => url('/promociones'),
                'icon' => 'bi-tags',
            ],
        ];

        if ($authenticated) {
            $sections = array_merge($sections, [
                [
                    'id' => 'carrito',
                    'type' => 'Sección personal',
                    'title' => 'Mi carrito',
                    'description' => 'Productos agregados al carrito de compras.',
                    'keywords' => 'carrito compra productos agregados',
                    'url' => $this->routeOrUrl(['carritos.index'], '/carritos'),
                    'icon' => 'bi-cart',
                ],
                [
                    'id' => 'perfil',
                    'type' => 'Sección personal',
                    'title' => 'Mi perfil',
                    'description' => 'Información personal de tu cuenta.',
                    'keywords' => 'perfil cuenta datos personales usuario',
                    'url' => url('/mi-perfil'),
                    'icon' => 'bi-person',
                ],
                [
                    'id' => 'pedidos',
                    'type' => 'Sección personal',
                    'title' => 'Mis pedidos',
                    'description' => 'Consulta el estado y detalle de tus pedidos.',
                    'keywords' => 'pedidos compras estado seguimiento',
                    'url' => url('/mis-pedidos'),
                    'icon' => 'bi-receipt',
                ],
            ]);
        }

        $normalizedTerm = $this->normalize($term);

        return collect($sections)
            ->filter(function (array $section) use ($normalizedTerm) {
                $searchableText = $this->normalize($section['title'] . ' ' . $section['description'] . ' ' . $section['keywords']);

                return Str::contains($searchableText, $normalizedTerm);
            })
            ->map(function (array $section) {
                unset($section['keywords']);

                return $section;
            })
            ->values();
    }

    private function firstExistingColumn(array $columns, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveDetailRoute(array $routeNames, mixed $id, string $fallback): string
    {
        if ($id !== null) {
            foreach ($routeNames as $routeName) {
                if (!Route::has($routeName)) {
                    continue;
                }

                try {
                    return route($routeName, $id);
                } catch (Throwable) {
                    continue;
                }
            }
        }

        return $fallback;
    }

    private function routeOrUrl(array $routeNames, string $fallback): string
    {
        foreach ($routeNames as $routeName) {
            if (Route::has($routeName)) {
                return route($routeName);
            }
        }

        return url($fallback);
    }

    private function normalize(string $value): string
    {
        return Str::lower(Str::ascii(trim($value)));
    }
}
