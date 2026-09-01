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
        $tag = \App\Models\Tag::create(['name' => 'Finance']);

        $response = $this->actingAs($user)->post('/computers', [
            'name' => 'Windows Accounting',
            'ip_address' => '192.168.1.20',
            'vnc_port' => '5900',
            'os_type' => 'windows',
            'location' => 'Ruang Server Lt 2',
            'description' => 'Server database utama unit IT',
            'vnc_password' => 'secret123',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect('/computers');

        $computer = Computer::query()->where('ip_address', '192.168.1.20')->firstOrFail();
        $this->assertSame('Windows Accounting', $computer->name);
        $this->assertSame(5900, $computer->vnc_port);
        $this->assertSame('Ruang Server Lt 2', $computer->location);
        $this->assertSame('Server database utama unit IT', $computer->description);
        $this->assertSame('secret123', $computer->vnc_password);
    }

    public function test_device_creation_requires_at_least_one_tag(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/computers', [
            'name' => 'Windows Accounting',
            'ip_address' => '192.168.1.20',
            'vnc_port' => '5900',
            'os_type' => 'windows',
        ]);

        $response->assertSessionHasErrors(['tag_ids']);
        $this->assertDatabaseCount('computers', 0);
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
        $tag = \App\Models\Tag::create(['name' => 'Server']);
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
            'tag_ids' => [$tag->id],
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
            ->assertJsonPath((string) $computer->id . '.vnc', false);
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

    public function test_devices_page_paginates_15_items_per_page(): void
    {
        $user = User::factory()->create();
        Computer::factory()->count(20)->create();

        $response = $this->actingAs($user)->get('/computers');

        $response->assertOk();
        $response->assertViewHas('computers', function ($computers) {
            return $computers->count() === 15 && $computers->total() === 20;
        });
    }

    public function test_user_can_search_devices_by_name_location_address_and_description(): void
    {
        $user = User::factory()->create();
        Computer::factory()->create(['name' => 'Alpha Server', 'ip_address' => '10.0.0.1']);
        Computer::factory()->create(['location' => 'Gedung Merdeka', 'ip_address' => '10.0.0.2']);
        Computer::factory()->create(['ip_address' => '192.168.99.88', 'vnc_port' => 5901]);
        Computer::factory()->create(['description' => 'Server Khusus POS Kasir']);
        Computer::factory()->create(['name' => 'Other PC', 'location' => 'Lobby', 'ip_address' => '172.16.0.1', 'description' => 'Unrelated']);

        // Search by device name
        $response = $this->actingAs($user)->get('/computers?search=Alpha');
        $response->assertOk();
        $response->assertSee('Alpha Server');
        $response->assertDontSee('Other PC');

        // Search by location
        $response = $this->actingAs($user)->get('/computers?search=Merdeka');
        $response->assertOk();
        $response->assertSee('Gedung Merdeka');
        $response->assertDontSee('Other PC');

        // Search by address (IP)
        $response = $this->actingAs($user)->get('/computers?search=192.168.99.88');
        $response->assertOk();
        $response->assertSee('192.168.99.88');
        $response->assertDontSee('Other PC');

        // Search by description
        $response = $this->actingAs($user)->get('/computers?search=POS+Kasir');
        $response->assertOk();
        $response->assertSee('Server Khusus POS Kasir');
        $response->assertDontSee('Other PC');
    }

    public function test_user_can_filter_devices_by_tag(): void
    {
        $user = User::factory()->create();
        $tag = \App\Models\Tag::create(['name' => 'Server Utama', 'color' => '#00828c']);

        $taggedComp = Computer::factory()->create(['name' => 'Tagged Machine']);
        $taggedComp->tagsRelation()->attach($tag->id);

        Computer::factory()->create(['name' => 'Untagged Machine']);

        $response = $this->actingAs($user)->get("/computers?tag={$tag->id}");
        $response->assertOk();
        $response->assertSee('Tagged Machine');
        $response->assertDontSee('Untagged Machine');
    }

    public function test_user_can_filter_devices_by_os(): void
    {
        $user = User::factory()->create();
        Computer::factory()->create(['name' => 'Win Server', 'os_type' => 'windows']);
        Computer::factory()->create(['name' => 'Linux Box', 'os_type' => 'linux']);

        $response = $this->actingAs($user)->get('/computers?os=windows');
        $response->assertOk();
        $response->assertSee('Win Server');
        $response->assertDontSee('Linux Box');

        $response = $this->actingAs($user)->get('/computers?os=linux');
        $response->assertOk();
        $response->assertSee('Linux Box');
        $response->assertDontSee('Win Server');
    }

    public function test_user_can_access_create_page_with_prefilled_duplicate_data(): void
    {
        $user = User::factory()->create();
        $original = Computer::factory()->create([
            'name' => 'POS Utama',
            'ip_address' => '192.168.1.50',
            'os_type' => 'linux',
            'location' => 'Kasir 1',
        ]);

        $response = $this->actingAs($user)->get("/computers/create?duplicate_from={$original->id}");

        $response->assertOk();
        $response->assertSee('POS Utama (Copy)');
        $response->assertSee('Duplicate Perangkat');
    }

    public function test_user_can_duplicate_a_device_with_copied_credentials(): void
    {
        $user = User::factory()->create();
        $tag = \App\Models\Tag::create(['name' => 'Retail']);
        $original = Computer::factory()->create([
            'name' => 'Kasir 1',
            'ip_address' => '192.168.1.10',
            'vnc_port' => 5900,
            'vnc_password' => 'secret_vnc_pass',
            'ssh_password' => 'secret_ssh_pass',
            'os_type' => 'linux',
            'location' => 'Lantai 1',
        ]);
        $original->tagsRelation()->attach($tag->id);

        $response = $this->actingAs($user)->post('/computers', [
            'duplicate_from_id' => $original->id,
            'name' => 'Kasir 2',
            'ip_address' => '192.168.1.11',
            'vnc_port' => 5900,
            'os_type' => 'linux',
            'location' => 'Lantai 1',
            'tag_ids' => [$tag->id],
            'copy_vnc_password' => 1,
            'copy_ssh_password' => 1,
        ]);

        $response->assertRedirect('/computers');
        $response->assertSessionHas('status');

        $duplicated = Computer::query()->where('ip_address', '192.168.1.11')->firstOrFail();
        $this->assertSame('Kasir 2', $duplicated->name);
        $this->assertSame('secret_vnc_pass', $duplicated->vnc_password);
        $this->assertSame('secret_ssh_pass', $duplicated->ssh_password);
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
