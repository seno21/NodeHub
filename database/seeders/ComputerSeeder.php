<?php

namespace Database\Seeders;

use App\Models\Computer;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComputerSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = [
            [
                'name' => 'Xubuntu Dev',
                'ip_address' => '192.168.1.10',
                'vnc_port' => 5900,
                'os_type' => 'linux',
                'location' => 'Lab Dev - Lantai 2',
                'description' => 'Komputer Development Xubuntu',
                'vnc_password' => null,
                'ssh_port' => 22,
                'ssh_user' => 'xubuntu',
                'ssh_password' => 'secret123',
                'refresh_command' => 'DISPLAY=:0 xdotool key F5',
                'tag_names' => ['Display'],
            ],
            [
                'name' => 'Windows Accounting',
                'ip_address' => '192.168.1.20',
                'vnc_port' => 5900,
                'os_type' => 'windows',
                'location' => 'Ruang Akuntansi - Lantai 1',
                'description' => 'Komputer Kasir & Akuntansi',
                'vnc_password' => 'secret123',
                'ssh_port' => 22,
                'ssh_user' => 'administrator',
                'ssh_password' => 'secret123',
                'refresh_command' => null,
                'tag_names' => ['Display'],
            ],
        ];

        foreach ($devices as $deviceData) {
            $tagNames = $deviceData['tag_names'] ?? [];
            unset($deviceData['tag_names']);

            /** @var Computer $computer */
            $computer = Computer::query()->firstOrCreate(['name' => $deviceData['name']], $deviceData);

            if (!empty($tagNames)) {
                $tags = Tag::query()->whereIn('name', $tagNames)->get();
                if ($tags->isNotEmpty()) {
                    $computer->tagsRelation()->sync($tags->pluck('id'));
                    $computer->update(['tags' => $tags->pluck('name')->implode(', ')]);
                }
            }
        }
    }
}
