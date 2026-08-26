<?php

namespace Database\Factories;

use App\Models\Computer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Computer>
 */
class ComputerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'PC-'.$this->faker->unique()->numerify('###'),
            'ip_address' => $this->faker->localIpv4(),
            'vnc_port' => 5900,
            'os_type' => $this->faker->randomElement(Computer::OS_TYPES),
            'vnc_password' => null,
        ];
    }
}
