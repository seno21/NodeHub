<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'name' => 'Display',
                'color' => '#00828c',
                'description' => 'Perangkat display di area poliklinik',
            ],
            [
                'name' => 'APM',
                'color' => '#10b981',
                'description' => 'Perangkat divisi keuangan & akuntansi',
            ]
        ];

        foreach ($tags as $tag) {
            Tag::query()->firstOrCreate(['name' => $tag['name']], $tag);
        }
    }
}
