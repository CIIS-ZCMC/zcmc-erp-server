<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'umis_employee_profile_id' => 'EMP' . fake()->unique()->randomNumber(5, true),
            'designation_id' => null,
            'division_id' => null,
            'department_id' => null,
            'section_id' => null,
            'unit_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'profile_url' => null,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }


}
