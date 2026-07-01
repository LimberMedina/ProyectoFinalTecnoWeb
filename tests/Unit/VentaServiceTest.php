<?php

namespace Tests\Unit;

use App\Services\VentaService;
use PHPUnit\Framework\TestCase;

class VentaServiceTest extends TestCase
{
    public function test_calcular_totales_uses_discounted_subtotal_without_double_discount(): void
    {
        $service = new VentaService();

        $detalles = [
            [
                'cantidad' => 2,
                'precio_unitario' => 50,
                'descuento' => 10,
                'subtotal' => 100,
            ],
        ];

        $totales = $service->calcularTotales($detalles);

        $this->assertSame(100.0, $totales['subtotal']);
        $this->assertSame(20.0, $totales['descuento']);
        $this->assertSame(100.0, $totales['total']);
    }
}
