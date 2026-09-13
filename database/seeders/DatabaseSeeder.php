<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (DB::table('roles')->count() === 0) {
            DB::table('roles')->insert([
                ['role_name' => 'Customer', 'created_at' => now(), 'updated_at' => now()],
                ['role_name' => 'Admin',    'created_at' => now(), 'updated_at' => now()],
                ['role_name' => 'Guide',    'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
