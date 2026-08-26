<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\RemoteAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoteActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_remote_actions(): void
    {
        $this->get('/actions')->assertRedirect('/login');
    }

    public function test_user_can_access_remote_actions_page(): void
    {
        $user = User::factory()->create();
        Computer::factory()->create(['name' => 'Display Kasir 1']);

        $response = $this->actingAs($user)->get('/actions');

        $response->assertOk();
        $response->assertSee('Remote Actions');
        $response->assertSee('Create Action');
    }

    public function test_user_can_create_remote_action_with_multiple_devices(): void
    {
        $user = User::factory()->create();
        $c1 = Computer::factory()->create(['name' => 'Device A']);
        $c2 = Computer::factory()->create(['name' => 'Device B']);

        $response = $this->actingAs($user)->post('/actions', [
            'name' => 'Refresh Both Displays',
            'icon' => 'lucide:refresh-cw',
            'description' => 'Refresh F5 kedua perangkat',
            'command' => 'DISPLAY=:0 xdotool key F5',
            'computer_ids' => [$c1->id, $c2->id],
        ]);

        $response->assertRedirect('/actions');

        $action = RemoteAction::query()->where('name', 'Refresh Both Displays')->firstOrFail();
        $this->assertSame('lucide:refresh-cw', $action->icon);
        $this->assertCount(2, $action->computers);
    }

    public function test_user_can_execute_remote_action(): void
    {
        $user = User::factory()->create();
        $c1 = Computer::factory()->create(['name' => 'Device A', 'ip_address' => '127.0.0.1']);

        $action = RemoteAction::query()->create([
            'name' => 'Test Action',
            'icon' => 'lucide:terminal',
            'command' => 'echo "hello"',
        ]);
        $action->computers()->attach($c1->id);

        $response = $this->actingAs($user)->postJson("/actions/{$action->id}/execute", [
            'computer_ids' => [$c1->id],
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'completed');
        $response->assertJsonPath('action_name', 'Test Action');
    }
}
