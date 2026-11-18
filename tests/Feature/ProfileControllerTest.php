<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_and_update_profile_in_database(): void
    {
        $user = User::factory()->create([
            'username' => 'alpet',
            'nama' => 'Alpet Lama',
            'email' => 'alpet@gmail.com',
        ]);

        $indexResponse = $this->actingAs($user)->get('/profil');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Alpet Lama');

        $updateResponse = $this->actingAs($user)->post('/profil', [
            'action' => 'save',
            'nama' => 'Alpet Baru',
            'email' => 'alpetbaru@gmail.com',
            'no_hp' => '08123456789',
            'nama_usaha' => 'Alpet Laundry',
            'kategori' => 'Jasa',
            'alamat' => 'Malang',
        ]);

        $updateResponse->assertRedirect('/profil');
        $updateResponse->assertSessionHas('flash.type', 'success');

        $user->refresh();
        $this->assertSame('Alpet Baru', $user->nama);
        $this->assertSame('alpetbaru@gmail.com', $user->email);
        $this->assertSame('Alpet Laundry', $user->nama_usaha);
        $this->assertSame('Jasa', $user->kategori_usaha);
        $this->assertSame('Malang', $user->alamat_usaha);
    }

    public function test_profile_update_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'other@example.com']);
        $user = User::factory()->create(['email' => 'me@example.com']);

        $response = $this->actingAs($user)->post('/profil', [
            'action' => 'save',
            'nama' => 'Test',
            'email' => 'other@example.com',
        ]);

        $response->assertRedirect('/profil');
        $response->assertSessionHas('flash.type', 'error');
        $user->refresh();
        $this->assertSame('me@example.com', $user->email);
    }
}
