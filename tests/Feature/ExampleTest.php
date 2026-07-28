<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_csrf_token_can_be_refreshed_before_a_long_running_form_submission()
    {
        $response = $this->getJson('/csrf-token');

        $response
            ->assertOk()
            ->assertJsonStructure(['token']);

        $this->assertNotEmpty($response->json('token'));
        $this->assertSame($response->json('token'), session()->token());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
