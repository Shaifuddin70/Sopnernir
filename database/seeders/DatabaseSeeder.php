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
        if (! User::query()->where('email', 'admin@example.com')->exists()) {
            User::factory()->admin()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }

        if (! User::query()->where('email', 'investor@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Investor One',
                'email' => 'investor@example.com',
            ]);
        }

        if (! User::query()->where('email', 'investor2@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Investor Two',
                'email' => 'investor2@example.com',
            ]);
        }
    }
}
