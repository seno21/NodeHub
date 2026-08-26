<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComputerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/computers/create')->assertRedirect('/login');
    }

    public function test_dashboard_displays_device_summary(): void
    {
        $user = User::factory()->create();
        Computer::factory()->count(2)->create(['os_type' => 'windows']);
        Computer::factory()->create(['os_type' => 'linux']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Total Devices');
        $response->assertSee('>3<', false);
    }

    public function test_devices_page_lists_devices(): void
    {
        $user = User::factory()->create();
        $computer = Computer::factory()->create(['name' => 'Xubuntu Dev']);

        $response = $this->actingAs($user)->get('/computers');

        $response->assertOk();
        $response->assertSee('Xubuntu Dev');
        $response->assertSee($computer->ip_address);
    }

    public function test_user_can_create_a_device(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/computers', [
            'name' => 'Windows Accounting',
            'ip_address' => '192.168.1.20',
            'vnc_port' => '5900',
            'os_type' => 'windows',
            'location' => 'Ruang Server Lt 2',
            'description' => 'Server database utama unit IT',
            'vnc_password' => 'secret123',
        ]);

        $response->assertRedirect('/computers');

        $computer = Computer::query()->where('ip_address', '192.168.1.20')->firstOrFail();
        $this->assertSame('Windows Accounting', $computer->name);
        $this->assertSame(5900, $computer->vnc_port);
        $this->assertSame('Ruang Server Lt 2', $computer->location);
        $this->assertSame('Server database utama unit IT', $computer->description);
        $this->assertSame('secret123', $computer->vnc_password);
    }

    public function test_device_creation_requires_valid_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/computers', [
            'name' => '',
            'ip_address' => 'not-an-ip',
            'vnc_port' => 99999,
            'os_type' => 'mac',
        ]);

        $response->assertSessionHasErrors(['name', 'ip_address', 'vnc_port', 'os_type']);
        $this->assertDatabaseCount('computers', 0);
    }

    public function test_user_can_update_a_device(): void
    {
        $user = User::factory()->create();
        $computer = Computer::factory()->create([
            'name' => 'Old Name',
            'vnc_password' => 'old-secret',
        ]);

        $response = $this->actingAs($user)->put("/computers/{$computer->id}", [
            'name' => 'New Name',
            'ip_address' => $computer->ip_address,
            'vnc_port' => (string) $computer->vnc_port,
            'os_type' => 'linux',
            'location' => 'Lab B 101',
            'description' => 'Updated desc',
            'vnc_password' => '',
        ]);

        $response->assertRedirect('/computers');

        $computer->refresh();
        $this->assertSame('New Name', $computer->name);
        $this->assertSame('linux', $computer->os_type);
        $this->assertSame('Lab B 101', $computer->location);
        $this->assertSame('Updated desc', $computer->description);
        $this->assertSame('old-secret', $computer->vnc_password, 'Empty password must keep the stored one.');
    }

    public function test_user_can_delete_a_device(): void
    {
        $user = User::factory()->create();
        $computer = Computer::factory()->create();

        $response = $this->actingAs($user)->delete("/computers/{$computer->id}");

        $response->assertRedirect('/computers');
        $this->assertDatabaseMissing('computers', ['id' => $computer->id]);
    }

    public function test_status_endpoint_reports_reachability_map(): void
    {
        $user = User::factory()->create();
        $computer = Computer::factory()->create();

        $response = $this->actingAs($user)->getJson('/computers/status');

        $response->assertOk()
            ->assertJsonPath((string) $computer->id, false);
    }

    public function test_ping_reports_single_device_status(): void
    {
        [$server, $host, $port] = $this->openLocalVncServer();

        try {
            $user = User::factory()->create();
            $online = Computer::factory()->create(['ip_address' => $host, 'vnc_port' => $port]);
            $offline = Computer::factory()->create(['ip_address' => $host, 'vnc_port' => 59000]);

            $this->actingAs($user)
                ->getJson("/computers/{$online->id}/ping")
                ->assertOk()
                ->assertJsonPath('online', true);

            $this->actingAs($user)
                ->getJson("/computers/{$offline->id}/ping")
                ->assertOk()
                ->assertJsonPath('online', false);
        } finally {
            fclose($server);
        }
    }

    /**
     * Open a temporary TCP listener simulating a reachable VNC target.
     *
     * @return array{0: resource, 1: string, 2: int}
     */
    private function openLocalVncServer(): array
    {
        $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        abort_if($server === false, 500, 'Cannot open test listener.');

        [, $port] = explode(':', (string) stream_socket_get_name($server, false));

        return [$server, '127.0.0.1', (int) $port];
    }
}
