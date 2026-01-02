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
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Seed specific users with roles
        $this->call(UserSeeder::class);

        // Create a test user with known credentials and completed profile
        User::factory()->withEmployee()->create([
            'email' => 'test@example.com',
        ]);

        // Create additional sample users with completed profiles
        User::factory(4)->withEmployee()->create();

        // Create some users without employee profiles (first-time login)
        User::factory(5)->create();

        // Create some employees without users (for testing purposes)
        \App\Models\Employee::factory(5)->create();
    }
}
