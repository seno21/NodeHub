<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_tags(): void
    {
        $this->get('/tags')->assertRedirect('/login');
    }

    public function test_user_can_access_tags_index(): void
    {
        $user = User::factory()->create();
        Tag::query()->create(['name' => 'Kasir Utama', 'color' => '#00828c']);

        $response = $this->actingAs($user)->get('/tags');

        $response->assertOk();
        $response->assertSee('Manajemen Tags Perangkat');
        $response->assertSee('#Kasir Utama');
    }

    public function test_user_can_create_tag(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tags', [
            'name' => 'Lantai 1',
            'color' => '#00828c',
            'description' => 'Perangkat di lantai 1',
        ]);

        $response->assertRedirect('/tags');
        $this->assertDatabaseHas('tags', [
            'name' => 'Lantai 1',
            'color' => '#00828c',
        ]);
    }

    public function test_user_can_create_computer_with_tags_from_table(): void
    {
        $user = User::factory()->create();
        $tag1 = Tag::query()->create(['name' => 'Display']);
        $tag2 = Tag::query()->create(['name' => 'Kasir']);

        $response = $this->actingAs($user)->post('/computers', [
            'name' => 'Komputer Test',
            'ip_address' => '192.168.1.50',
            'vnc_port' => 5900,
            'os_type' => 'linux',
            'tag_ids' => [$tag1->id, $tag2->id],
        ]);

        $response->assertRedirect('/computers');

        $computer = Computer::query()->where('name', 'Komputer Test')->firstOrFail();
        $this->assertCount(2, $computer->tagsRelation);
        $this->assertStringContainsString('Display', $computer->tags);
    }
}
