<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_api_returns_a_successful_response(): void
    {
        $response = $this->get('/api/products');

        $response->assertStatus(200);
    }
}
