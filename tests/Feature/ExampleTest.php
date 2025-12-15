<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that the login page returns a successful response.
     * 
     * Route '/' redirects to '/login', so we test the login page directly.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Test the login page directly since '/' redirects to '/login'
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test that root route redirects to login page.
     */
    public function test_root_route_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302)
                 ->assertRedirect('/login');
    }
}
