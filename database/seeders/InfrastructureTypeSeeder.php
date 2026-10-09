<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InfrastructureType;

class InfrastructureTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InfrastructureType::insert([
            [
                'name' => 'Bridge',
                'slug' => 'bridge',
                'description' => 'Structures built to span physical obstacles without closing the way underneath.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Road',
                'slug' => 'road',
                'description' => 'Linear ways for the conveyance of traffic that mostly have an improved surface.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Building',
                'slug' => 'building',
                'description' => 'Structures with a roof and walls, such as a house or factory.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hospital',
                'slug' => 'hospital',
                'description' => 'Healthcare institutions providing patient treatment with specialized staff and equipment.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
