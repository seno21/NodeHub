<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\User;
use App\Services\RemoteActionService;
use App\Services\VncSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ssh_connection_check_flags_missing_password(): void
    {
        $server = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if (!$server) {
            $this->markTestSkipped('Unable to open dummy socket server');
        }

        $name = stream_socket_get_name($server, false);
        $port = (int) parse_url('tcp://' . $name, PHP_URL_PORT);

        try {
            $computer = Computer::factory()->create([
                'ip_address' => '127.0.0.1',
                'ssh_port' => $port,
                'ssh_password' => null,
            ]);

            $service = new RemoteActionService();
            $result = $service->checkSshConnection($computer);

            $this->assertFalse($result['success']);
            $this->assertSame('password_missing', $result['error_type']);
            $this->assertStringContainsString('Password SSH belum diatur', $result['message']);
        } finally {
            fclose($server);
        }
    }

    public function test_ssh_connection_check_flags_closed_port_or_unreachable(): void
    {
        // Port 59999 is closed locally
        $computer = Computer::factory()->create([
            'ip_address' => '127.0.0.1',
            'ssh_port' => 59999,
            'ssh_password' => 'somepassword',
        ]);

        $service = new RemoteActionService();
        $result = $service->checkSshConnection($computer);

        $this->assertContains($result['error_type'], ['port_closed', 'timeout', 'connection_failed']);
        $this->assertTrue(
            str_contains($result['message'], 'PORT SSH TERTUTUP') ||
            str_contains($result['message'], 'KONEKSI TIMEOUT') ||
            str_contains($result['message'], 'KONEKSI GAGAL')
        );
    }

    public function test_ping_diagnostics_returns_detailed_error_messages(): void
    {
        $computer = Computer::factory()->create([
            'ip_address' => '127.0.0.1',
            'vnc_port' => 59998,
            'ssh_port' => 59999,
            'ssh_password' => 'somepassword',
        ]);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get("/computers/{$computer->id}/ping");

        $response->assertOk();
        $response->assertJsonStructure([
            'online',
            'vnc_ok',
            'vnc_error_type',
            'vnc_error_message',
            'ssh_ok',
            'ssh_auth_ok',
            'ssh_error_type',
            'ssh_error_message',
            'icmp_ok',
        ]);
    }
}
