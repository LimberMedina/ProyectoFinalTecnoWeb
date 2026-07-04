<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductoQrGenerationTest extends TestCase
{
    public function test_it_generates_a_qr_for_the_product_code(): void
    {
        Storage::disk('public')->deleteDirectory('productos/qr');

        $response = $this->postJson(route('productos.generate-qr'), [
            'codigo' => 'PRD-001',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'qr_path',
            'qr_url',
        ]);

        $this->assertNotEmpty($response->json('qr_path'));
        $this->assertTrue(Storage::disk('public')->exists($response->json('qr_path')));
    }
}
