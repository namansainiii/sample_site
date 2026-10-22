<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_and_register_pages_are_accessible(): void
    {
        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
    }

    public function test_unauthenticated_requests_are_locked_and_redirected(): void
    {
        $this->get('/catalog')->assertRedirect('/login');
        $this->get('/cart')->assertRedirect('/login');
        $this->get('/checkout')->assertRedirect('/login');
        $this->get('/admin/orders')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_store_pages(): void
    {
        $this->withCookie('demo_logged_in', 'client')->get('/catalog')->assertStatus(200);
        $this->withCookie('demo_logged_in', 'client')->get('/cart')->assertStatus(200);
        $this->withCookie('demo_logged_in', 'client')->get('/checkout')->assertStatus(200);
        $this->withCookie('demo_logged_in', 'admin')->get('/admin/orders')->assertStatus(200);
    }
}
