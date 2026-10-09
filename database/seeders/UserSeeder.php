<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@bridgecare.gov',
            'phone' => '+91 80 4610 4610',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
            'profile_image' => null,
        ]);

        // Staff users
        $staffNames = [
            'Rajesh Kumar',
            'Priya Sharma',
            'Amit Patel',
            'Sneha Reddy',
            'Vikram Singh',
            'Ananya Das',
        ];

        foreach ($staffNames as $name) {
            User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@bridgecare.gov',
                'phone' => '+91 ' . rand(7000000000, 9999999999),
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'status' => 'active',
                'profile_image' => null,
            ]);
        }

        // A couple of inactive users for demo
        User::create([
            'name' => 'Former Employee',
            'email' => 'former@bridgecare.gov',
            'phone' => '+91 90000 00001',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'status' => 'inactive',
            'profile_image' => null,
        ]);
    }
}
