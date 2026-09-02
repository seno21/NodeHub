<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_export_devices_as_json(): void
    {
        $user = User::factory()->create();
        $computer = Computer::factory()->create([
            'name' => 'Server Main',
            'ip_address' => '192.168.1.10',
            'vnc_port' => 5900,
            'vnc_password' => 'vncsecret',
            'ssh_port' => 22,
            'ssh_user' => 'admin',
            'ssh_password' => 'sshsecret',
        ]);

        $response = $this->actingAs($user)->get('/computers/export?format=json');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');

        $content = json_decode($response->streamedContent(), true);
        $this->assertSame('NodeHub', $content['app']);
        $this->assertCount(1, $content['devices']);
        $this->assertSame('Server Main', $content['devices'][0]['name']);
        $this->assertSame('192.168.1.10', $content['devices'][0]['ip_address']);
        $this->assertSame('vncsecret', $content['devices'][0]['vnc_password']);
        $this->assertSame('sshsecret', $content['devices'][0]['ssh_password']);
    }

    public function test_user_can_export_devices_as_csv(): void
    {
        $user = User::factory()->create();
        Computer::factory()->create([
            'name' => 'Workstation A',
            'ip_address' => '192.168.1.15',
        ]);

        $response = $this->actingAs($user)->get('/computers/export?format=csv');

        $response->assertOk();
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
    }

    public function test_user_can_import_devices_from_json_backup(): void
    {
        $user = User::factory()->create();

        $jsonData = json_encode([
            'app' => 'NodeHub',
            'devices' => [
                [
                    'name' => 'Imported PC 1',
                    'ip_address' => '10.0.0.50',
                    'os_type' => 'windows',
                    'vnc_port' => 5900,
                    'vnc_password' => 'vncpass123',
                    'ssh_port' => 22,
                    'ssh_user' => 'xubuntu',
                    'ssh_password' => 'sshpass123',
                    'location' => 'Lab Komputer',
                    'description' => 'Test import json',
                    'tags' => ['Lab', 'Windows'],
                ],
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('backup.json', $jsonData);

        $response = $this->actingAs($user)->post('/computers/import', [
            'backup_file' => $file,
            'duplicate_action' => 'skip',
        ]);

        $response->assertRedirect('/computers');
        $response->assertSessionHas('status');

        $computer = Computer::where('ip_address', '10.0.0.50')->firstOrFail();
        $this->assertSame('Imported PC 1', $computer->name);
        $this->assertSame('vncpass123', $computer->vnc_password);
        $this->assertSame('sshpass123', $computer->ssh_password);
        $this->assertTrue($computer->tagsRelation()->where('name', 'Lab')->exists());
    }

    public function test_user_can_import_devices_and_update_existing(): void
    {
        $user = User::factory()->create();
        $existing = Computer::factory()->create([
            'name' => 'Old Name',
            'ip_address' => '192.168.1.99',
            'vnc_password' => 'oldvnc',
        ]);

        $jsonData = json_encode([
            'devices' => [
                [
                    'name' => 'Updated Name',
                    'ip_address' => '192.168.1.99',
                    'vnc_password' => 'newvnc123',
                ],
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('backup.json', $jsonData);

        $response = $this->actingAs($user)->post('/computers/import', [
            'backup_file' => $file,
            'duplicate_action' => 'update',
        ]);

        $response->assertRedirect('/computers');

        $updated = Computer::findOrFail($existing->id);
        $this->assertSame('Updated Name', $updated->name);
        $this->assertSame('192.168.1.99', $updated->ip_address);
        $this->assertSame('newvnc123', $updated->vnc_password);
    }
}
