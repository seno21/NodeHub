<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primary
        User::query()->updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => 'qwerty21',
            ],
        );

        $this->call([
            TagSeeder::class,
            ComputerSeeder::class,
            RemoteActionSeeder::class,
        ]);
    }
}
