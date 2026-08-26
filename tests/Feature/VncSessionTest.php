<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\User;
use App\Services\VncSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VncSessionTest extends TestCase
{
    use RefreshDatabase;

    private string $tokenFile;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'vnc.websockify.token_file' => $this->tokenFile = sys_get_temp_dir().'/vnc-tokens-test-'.uniqid().'.cfg',
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

    public function test_connect_creates_session_and_redirects_to_viewer(): void
    {
        [$server, $host, $port] = $this->openLocalVncServer();

        try {
            $user = User::factory()->create();
            $computer = Computer::factory()->create([
                'ip_address' => $host,
                'vnc_port' => $port,
                'vnc_password' => 'secret123',
            ]);

            $response = $this->actingAs($user)->post("/computers/{$computer->id}/connect");

            $response->assertRedirect();

            $token = basename((string) parse_url($response->headers->get('Location'), PHP_URL_PATH));

            $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}$/', $token);

            $lines = file($this->tokenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $this->assertCount(1, $lines);
            $this->assertSame("{$token}: {$host}:{$port}", $lines[0]);

            $session = app(VncSessionService::class)->getSession($token);
            $this->assertNotNull($session);
            $this->assertSame('secret123', $session['vnc_password']);
        } finally {
            fclose($server);
        }
    }

    public function test_connect_is_blocked_when_target_is_unreachable(): void
    {
        $user = User::factory()->create();
        $computer = Computer::factory()->create([
            'ip_address' => '127.0.0.1',
            'vnc_port' => 59000,
        ]);

        // JSON client (fetch from dashboard)
        $this->actingAs($user)
            ->postJson("/computers/{$computer->id}/connect")
            ->assertStatus(503)
            ->assertJsonFragment(['message' => "\"{$computer->name}\" is unreachable on 127.0.0.1:59000 — remote session was not started."]);

        // Regular form client
        $this->actingAs($user)
            ->post("/computers/{$computer->id}/connect")
            ->assertRedirect()
            ->assertSessionHasErrors('connect');

        $this->assertDatabaseMissing('computers', ['vnc_password' => 'never-created-session']);
    }

    public function test_ticket_returns_connection_data_for_valid_token(): void
    {
        $user = User::factory()->create();
        $computer = Computer::factory()->create(['vnc_password' => 'secret123']);

        $token = app(VncSessionService::class)->createSession($computer);

        $response = $this->actingAs($user)->getJson("/vnc/ticket/{$token}");

        $response->assertOk()
            ->assertJsonPath('password', 'secret123')
            ->assertJsonPath('device_name', $computer->name)
            ->assertJsonPath('ws_url', "ws://localhost:6080/websockify?token={$token}");
    }

    public function test_ticket_is_rejected_for_unknown_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/vnc/ticket/'.str_repeat('a', 40))
            ->assertNotFound();
    }

    public function test_ticket_derives_ws_url_from_request_host_when_unconfigured(): void
    {
        config(['vnc.websockify.ws_url' => null]);
        config(['vnc.websockify.listen' => '0.0.0.0:6080']);

        $user = User::factory()->create();
        $computer = Computer::factory()->create();

        $token = app(VncSessionService::class)->createSession($computer);

        $this->actingAs($user)
            ->getJson("http://10.0.3.5:8000/vnc/ticket/{$token}")
            ->assertOk()
            ->assertJsonPath('ws_url', "ws://10.0.3.5:8000/websockify?token={$token}");
    }

    public function test_expired_sessions_are_pruned_from_token_file(): void
    {
        $computer = Computer::factory()->create();
        $service = app(VncSessionService::class);

        $liveToken = $service->createSession($computer);

        // Simulate an expired session still present in the token file.
        $deadToken = str_repeat('b', 40);
        file_put_contents(
            $this->tokenFile,
            "{$deadToken}: 10.0.0.99:5900".PHP_EOL,
            FILE_APPEND,
        );

        Cache::forget("vnc-session:{$deadToken}");

        $removed = $service->prune();

        $this->assertSame(1, $removed);

        $lines = file($this->tokenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertCount(1, $lines);
        $this->assertStringStartsWith($liveToken.':', $lines[0]);
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
