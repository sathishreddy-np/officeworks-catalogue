<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response()
    {
        // The home page now loads products. Give it an empty list so this test never calls the internet.
        Http::preventStrayRequests();
        Http::fake([
            'fakestoreapi.com/*' => Http::response([]),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
    }
}
