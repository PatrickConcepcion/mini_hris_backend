<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $employeeNumber = 1;

        return [
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->firstName(),
            'last_name' => fake()->lastName(),
            'personal_email' => fake()->unique()->safeEmail(),
            'employee_no' => 'EMP-' . str_pad($employeeNumber++, 5, '0', STR_PAD_LEFT),
            'date_of_birth' => fake()->date('Y-m-d', '-18 years'),
            'date_hired' => fake()->date('Y-m-d', '-5 years'),
            'phone_number' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'zip_code' => fake()->postcode(),
            'country' => fake()->country(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'nationality' => fake()->country(),
            'marital_status' => fake()->randomElement(['single', 'married', 'divorced', 'widowed']),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
            'emergency_contact_relationship' => fake()->randomElement(['spouse', 'parent', 'sibling', 'friend']),
            'height_cm' => fake()->numberBetween(150, 200),
            'weight_kg' => fake()->numberBetween(50, 120),
        ];
    }
}
