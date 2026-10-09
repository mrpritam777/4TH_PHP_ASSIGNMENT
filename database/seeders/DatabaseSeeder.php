<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            InfrastructureTypeSeeder::class,
            InfrastructureSeeder::class,
            MaintenanceSeeder::class,
            ContactSeeder::class,
        ]);

        // Uncomment the below to run the seeder
        // $this->call([UserSeeder::class]);
    }
}
