<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The local Admin login is test@example.com / password. In production,
     * create real logins with `php artisan cms:create-admin` instead.
     */
    public function run(): void
    {
        if (! app()->isProduction()) {
            User::factory()->admin()->create([
                'name' => 'Test Admin',
                'email' => 'test@example.com',
            ]);
        }

        $this->call(ContentSeeder::class);
    }
}
