<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_can_register_and_password_is_hashed(): void
    {
        $response = $this->post('/login', [
            'action' => 'register',
            'nama' => 'Budi Santoso',
            'username' => 'budi_umkm',
            'email' => 'budi@example.com',
            'no_hp' => '081234567890',
            'nama_usaha' => 'Toko Budi',
            'kategori' => 'Makanan & Minuman',
            'alamat' => 'Malang',
            'password' => 'rahasia123',
            'password2' => 'rahasia123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'Akun berhasil didaftarkan! Silakan masuk.');

        $user = User::where('username', 'budi_umkm')->first();
        $this->assertNotNull($user);
        $this->assertSame('budi@example.com', $user->email);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_registration_fails_when_username_is_duplicate(): void
    {
        User::factory()->create(['username' => 'budi_umkm']);

        $response = $this->post('/login', [
            'action' => 'register',
            'nama' => 'Budi Lain',
            'username' => 'budi_umkm',
            'email' => 'budi2@example.com',
            'password' => 'rahasia123',
            'password2' => 'rahasia123',
        ]);

        $response->assertRedirect('/login?tab=register');
        $response->assertSessionHas('error', 'Username sudah terdaftar.');
    }

    public function test_user_can_login_with_username_and_logout(): void
    {
        $user = User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => 'admin123',
        ]);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
            'action' => 'login',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $logoutResponse = $this->post('/logout', [
            'action' => 'logout',
        ]);

        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_can_login_with_email_case_insensitively_and_get_logout(): void
    {
        $user = User::factory()->create([
            'username' => 'alpet',
            'email' => 'alpet@gmail.com',
            'password' => 'alpet123',
        ]);

        $response = $this->post('/login', [
            'username' => 'ALPET@GMAIL.COM',
            'password' => 'alpet123',
            'action' => 'login',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $getLogout = $this->get('/logout');

        $getLogout->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'salah123',
            'action' => 'login',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Email / Username atau kata sandi salah.');
        $this->assertGuest();
    }
}
