<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_lock_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/lock');

        $response->assertStatus(200);
        $response->assertSee('Sesi Terkunci');
    }

    public function test_user_can_manually_lock_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/lock-session');

        $response->assertRedirect('/lock');

        // Subsequent requests should be redirected to lock
        $dashboardResponse = $this->actingAs($user)->get('/dashboard');
        $dashboardResponse->assertRedirect('/lock');
    }

    public function test_user_cannot_unlock_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)->withSession(['session_locked' => true]);

        $response = $this->post('/lock', [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_user_can_unlock_with_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)->withSession(['session_locked' => true]);

        $response = $this->post('/lock', [
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');

        // Subsequent requests should load successfully
        $dashboardResponse = $this->actingAs($user)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
    }

    public function test_inactivity_exceeding_20_minutes_locks_session(): void
    {
        $user = User::factory()->create();

        // Simulate activity 21 minutes ago (1260 seconds ago)
        $response = $this->actingAs($user)->withSession([
            'last_activity' => time() - 1260,
        ])->get('/dashboard');

        $response->assertRedirect('/lock');
    }

    public function test_user_can_customise_auto_lock_timeout_setting(): void
    {
        $user = User::factory()->create([
            'auto_lock_timeout' => 5, // 5 minutes
        ]);

        // 4 minutes ago (240s) should NOT lock
        $validResponse = $this->actingAs($user)->withSession([
            'last_activity' => time() - 240,
        ])->get('/dashboard');

        $validResponse->assertStatus(200);

        // 6 minutes ago (360s) SHOULD lock for 5-minute timeout setting
        $lockedResponse = $this->actingAs($user)->withSession([
            'last_activity' => time() - 360,
        ])->get('/dashboard');

        $lockedResponse->assertRedirect('/lock');
    }
}
