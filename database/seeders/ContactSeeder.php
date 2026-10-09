<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\Infrastructure;
use Illuminate\Support\Str;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $infrastructures = Infrastructure::all();

        $contactTypes = ['emergency', 'government', 'maintenance', 'hospital', 'contractor', 'other'];

        $names = [
            'Emergency Control Room',
            'Regional Transport Office',
            'Public Works Department',
            'State Electricity Board',
            'Water Supply Authority',
            'National Highways Authority',
            'Bridge Maintenance Corp',
            'Road Safety Council',
            'Urban Development Authority',
            'Disaster Management Cell',
        ];

        $organizations = [
            'Government of India',
            'State Public Works Department',
            'National Highway Authority of India',
            'State Road Transport Corporation',
            'Municipal Corporation',
            'Police Department',
            'Fire Services',
            'Disaster Management Authority',
        ];

        foreach ($infrastructures as $infra) {
            // Create 1-3 contacts per infrastructure
            $contactCount = rand(1, 3);
            for ($i = 0; $i < $contactCount; $i++) {
                $type = $contactTypes[array_rand($contactTypes)];
                $name = $names[array_rand($names)];
                $org = $organizations[array_rand($organizations)];

                Contact::create([
                    'infrastructure_id' => $infra->id,
                    'name' => $name,
                    'organization' => $org,
                    'department' => $type === 'emergency' ? 'Emergency Services' : ($type === 'hospital' ? 'Medical Services' : 'Maintenance Division'),
                    'designation' => $type === 'emergency' ? 'Emergency Response Coordinator' : ($type === 'hospital' ? 'Chief Medical Officer' : 'Maintenance Engineer'),
                    'phone' => '+91 ' . rand(7000000000, 9999999999),
                    'email' => strtolower(str_replace(' ', '.', $name)) . '@' . strtolower(str_replace(' ', '', $org)) . '.gov.in',
                    'address' => $infra->address . ', ' . $infra->location,
                    'contact_type' => $type,
                ]);
            }
        }
    }
}
