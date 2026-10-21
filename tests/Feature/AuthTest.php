<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_user_can_login_and_logout(): void
    {
        // 1. Login with admin
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
            'action' => 'login',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertEquals('admin', session('user'));

        // 2. Logout
        $logoutResponse = $this->post('/logout', [
            'action' => 'logout',
        ]);

        $logoutResponse->assertRedirect('/login');
        $this->assertNull(session('user'));

        // 3. GET /logout should also work
        $this->withSession(['user' => 'admin']);
        $getLogoutResponse = $this->get('/logout');
        $getLogoutResponse->assertRedirect('/login');
        $this->assertNull(session('user'));
    }
}
