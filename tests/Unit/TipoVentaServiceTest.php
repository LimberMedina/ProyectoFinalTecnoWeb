<?php

namespace Tests\Unit;

use App\Services\TipoVentaService;
use Tests\TestCase;

class TipoVentaServiceTest extends TestCase
{
    public function test_it_returns_mayorista_when_any_item_is_wholesale(): void
    {
        $service = new TipoVentaService();

        $tipoVenta = $service->resolverDesdeItems([
            ['tipo_venta' => 'minorista', 'cantidad' => 1],
            ['tipo_venta' => 'mayorista', 'cantidad' => 3],
        ]);

        $this->assertSame('mayorista', $tipoVenta);
    }

    public function test_it_defaults_to_minorista_when_no_item_is_wholesale(): void
    {
        $service = new TipoVentaService();

        $tipoVenta = $service->resolverDesdeItems([
            ['tipo_venta' => 'minorista', 'cantidad' => 2],
            ['tipo_venta' => 'minorista', 'cantidad' => 1],
        ]);

        $this->assertSame('minorista', $tipoVenta);
    }
}
