<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_displays(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'tegar@sekolah.sch.id',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'tegar@sekolah.sch.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/tickets');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'tegar@sekolah.sch.id',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'tegar@sekolah.sch.id',
            'password' => 'salah-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@sekolah.sch.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->post('/login', [
            'email' => 'admin@sekolah.sch.id',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_technician_is_redirected_to_technician_dashboard(): void
    {
        User::factory()->create([
            'email' => 'teknisi@sekolah.sch.id',
            'password' => bcrypt('password'),
            'role' => 'technician',
        ]);

        $this->post('/login', [
            'email' => 'teknisi@sekolah.sch.id',
            'password' => 'password',
        ])->assertRedirect(route('technician.dashboard'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'nonaktif@sekolah.sch.id',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'nonaktif@sekolah.sch.id',
            'password' => 'password',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->get('/tickets')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
