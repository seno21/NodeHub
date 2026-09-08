<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\User;
use App\Services\VncSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FastConnectTest extends TestCase
{
    use RefreshDatabase;

    private string $tokenFile;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'vnc.websockify.token_file' => $this->tokenFile = sys_get_temp_dir().'/vnc-tokens-fastconnect-test-'.uniqid().'.cfg',
            'vnc.websockify.ws_url' => 'ws://localhost:6080',
            'vnc.token_ttl' => 120,
        ]);
    }

    protected function tearDown(): void
    {
        if (is_file($this->tokenFile)) {
            unlink($this->tokenFile);
        }

        parent::tearDown();
    }

    public function test_fast_connect_validation_requires_ip(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/vnc/fast-connect', [
                'ip_address' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('ip_address');
    }

    public function test_fast_connect_creates_session_and_redirects(): void
    {
        [$server, $host, $port] = $this->openLocalVncServer();

        try {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->postJson('/vnc/fast-connect', [
                    'ip_address' => $host,
                    'vnc_port' => $port,
                    'vnc_password' => 'fastpass123',
                    'os_type' => 'linux',
                ]);

            $response->assertOk()
                ->assertJsonStructure(['redirect']);

            $redirectUrl = $response->json('redirect');
            $token = basename((string) parse_url($redirectUrl, PHP_URL_PATH));

            $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}$/', $token);

            $lines = file($this->tokenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $this->assertCount(1, $lines);
            $this->assertSame("{$token}: {$host}:{$port}", $lines[0]);

            $session = app(VncSessionService::class)->getSession($token);
            $this->assertNotNull($session);
            $this->assertSame('fastpass123', $session['vnc_password']);
            $this->assertTrue($session['is_fast_connect']);
        } finally {
            fclose($server);
        }
    }

    public function test_fast_connect_can_save_device_to_database(): void
    {
        [$server, $host, $port] = $this->openLocalVncServer();

        try {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->postJson('/vnc/fast-connect', [
                    'ip_address' => $host,
                    'vnc_port' => $port,
                    'vnc_password' => 'savedpass123',
                    'os_type' => 'windows',
                    'save_device' => true,
                    'device_name' => 'PC Kasir FastConnect',
                ]);

            $response->assertOk();

            $this->assertDatabaseHas('computers', [
                'name' => 'PC Kasir FastConnect',
                'ip_address' => $host,
                'vnc_port' => $port,
                'os_type' => 'windows',
            ]);

            $computer = Computer::where('name', 'PC Kasir FastConnect')->first();
            $this->assertNotNull($computer);
            $this->assertSame('savedpass123', $computer->vnc_password);
        } finally {
            fclose($server);
        }
    }

    public function test_fast_connect_returns_error_when_target_unreachable(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/vnc/fast-connect', [
                'ip_address' => '127.0.0.1',
                'vnc_port' => 59888,
            ]);

        $response->assertStatus(503);
        $this->assertTrue(
            str_contains($response->json('message'), 'PORT VNC TERTUTUP') ||
            str_contains($response->json('message'), 'unreachable') ||
            str_contains($response->json('message'), 'gagal')
        );
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
