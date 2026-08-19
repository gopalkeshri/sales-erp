<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200)
            ->assertSee('SALES ERP')
            ->assertSee('Sign In to ERP Portal')
            ->assertSee('1-Click Role Login Demo');
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@saleserp.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@saleserp.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_dashboard_and_logout(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();

        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200)
            ->assertSee('Executive Dashboard')
            ->assertSee($admin->name);

        $logoutResponse = $this->actingAs($admin)->post('/logout');
        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();
    }
}
