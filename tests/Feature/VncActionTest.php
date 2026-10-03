<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\User;
use App\Services\VncActionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VncActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_vnc_refresh_endpoint_requires_authentication(): void
    {
        $response = $this->postJson('/computers/vnc-refresh', []);
        $response->assertUnauthorized();
    }

    public function test_vnc_refresh_endpoint_executes_batch_refresh(): void
    {
        $user = User::factory()->create();
        $computer1 = Computer::factory()->create(['name' => 'PC 1', 'ip_address' => '10.250.1.1', 'vnc_port' => 5900]);
        $computer2 = Computer::factory()->create(['name' => 'PC 2', 'ip_address' => '10.250.1.2', 'vnc_port' => 5900]);

        $response = $this->actingAs($user)->postJson('/computers/vnc-refresh', [
            'computer_ids' => [$computer1->id, $computer2->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('total', 2)
            ->assertJsonPath('action', 'VNC F5 Refresh (No SSH)');

        $results = $response->json('results');
        $this->assertCount(2, $results);
        $this->assertFalse($results[0]['success']); // Expected unreachable port in test env
        $this->assertStringContainsString('KONEKSI TIMEOUT', $results[0]['message']);
    }

    public function test_vnc_action_service_handles_unreachable_target(): void
    {
        $computer = Computer::factory()->create([
            'ip_address' => '127.0.0.1',
            'vnc_port' => 59999,
        ]);

        $service = app(VncActionService::class);
        $result = $service->sendF5RefreshToComputer($computer);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('PORT VNC TERTUTUP', $result['message']);
    }
}
