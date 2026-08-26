<?php

namespace Database\Seeders;

use App\Models\Computer;
use App\Models\RemoteAction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RemoteActionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $computers = Computer::all();

        $actions = [
            [
                'name' => 'Refresh Firefox (F5)',
                'icon' => 'lucide:refresh-cw',
                'description' => 'Kirim F5 keypress ke display Xubuntu/Firefox',
                'command' => 'DISPLAY=:0 xdotool key F5',
                'all_computers' => true,
            ],
            [
                'name' => 'Restart Firefox',
                'icon' => 'logos:firefox',
                'description' => 'Restart firefox browser',
                'command' => 'killall -9 firefox 2>/dev/null; DISPLAY=:0 XAUTHORITY=$HOME/.Xauthority firefox &',
                'target_computer' => 'Display Poli 117',
            ],
            [
                'name' => 'Install Xdotool',
                'icon' => 'ri:install-fill',
                'description' => 'Install xdotool, password, username dan ip baca dari database',
                'command' => 'echo $SSH_PASS | sudo -S apt-get update && echo $SSH_PASS | sudo -S apt-get install -y xdotool',
                'target_computer' => 'Display Poli 117',
            ],
        ];

        foreach ($actions as $actionData) {
            $allComputers = $actionData['all_computers'] ?? false;
            $targetComputerName = $actionData['target_computer'] ?? null;
            unset($actionData['all_computers'], $actionData['target_computer']);

            $action = RemoteAction::query()->firstOrCreate(
                ['name' => $actionData['name']],
                $actionData
            );

            if ($allComputers) {
                if ($computers->isNotEmpty()) {
                    $action->computers()->sync($computers->pluck('id'));
                }
            } elseif ($targetComputerName) {
                $target = $computers->firstWhere('name', $targetComputerName);
                if ($target) {
                    $action->computers()->syncWithoutDetaching([$target->id]);
                } elseif ($computers->isNotEmpty()) {
                    $action->computers()->syncWithoutDetaching($computers->pluck('id'));
                }
            }
        }
    }
}
