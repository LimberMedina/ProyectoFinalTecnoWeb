<?php

namespace App\Services;

class TipoVentaService
{
    public function resolverDesdeItems(array $items): string
    {
        foreach ($items as $item) {
            $tipoVenta = strtoupper((string) ($item['tipo_venta'] ?? 'minorista'));

            if ($tipoVenta === 'MAYORISTA') {
                return 'mayorista';
            }
        }

        return 'minorista';
    }
}
