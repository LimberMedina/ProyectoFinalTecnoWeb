<?php

namespace Tests\Feature;

use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    public function test_global_search_endpoint_returns_results_payload(): void
    {
        $response = $this->getJson(route('search.global', ['q' => 'producto']));

        $response->assertOk()
            ->assertJsonStructure([
                'query',
                'results',
            ])
            ->assertJsonPath('query', 'producto');
    }
}
