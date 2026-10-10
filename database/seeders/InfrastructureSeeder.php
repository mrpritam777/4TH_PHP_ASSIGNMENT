<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Infrastructure;
use App\Models\BridgeDetails;
use App\Models\RoadDetails;
use App\Models\BuildingDetails;
use App\Models\HospitalDetails;
use Illuminate\Support\Str;

class InfrastructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bridgeType = \App\Models\InfrastructureType::where('slug', 'bridge')->first();
        $roadType = \App\Models\InfrastructureType::where('slug', 'road')->first();
        $buildingType = \App\Models\InfrastructureType::where('slug', 'building')->first();
        $hospitalType = \App\Models\InfrastructureType::where('slug', 'hospital')->first();

        $adminId = \App\Models\User::where('role', 'admin')->first()->id ?? 1;

        // Bridges
        $bridges = [
            [
                'name' => 'Howrah Bridge',
                'code' => 'BRG-001',
                'location' => 'Kolkata, West Bengal',
                'address' => 'Hooghly River, Kolkata 700001',
                'latitude' => '22.585000',
                'longitude' => '88.350000',
                'construction_year' => 1943,
                'condition' => 'good',
                'description' => 'A balanced cantilever bridge over the Hooghly River in Kolkata, West Bengal.',
            ],
            [
                'name' => 'Pamban Bridge',
                'code' => 'BRG-002',
                'location' => 'Rameswaram, Tamil Nadu',
                'address' => 'Palk Strait, Rameswaram 623526',
                'latitude' => '9.269600',
                'longitude' => '79.224100',
                'construction_year' => 1914,
                'condition' => 'fair',
                'description' => 'A cantilever bridge connecting the town of Rameswaram on Pamban Island to mainland India.',
            ],
            [
                'name' => 'Bandra-Worli Sea Link',
                'code' => 'BRG-003',
                'location' => 'Mumbai, Maharashtra',
                'address' => 'Western Express Highway, Bandra 400050',
                'latitude' => '19.025600',
                'longitude' => '72.848300',
                'construction_year' => 2009,
                'condition' => 'excellent',
                'description' => 'A cable-stayed bridge spanning the Mahim Bay in Mumbai.',
            ],
        ];

        $bridgeImages = [
            'Howrah Bridge' => '/assets/images/howrahBridge.jpg',
            'Pamban Bridge' => '/assets/images/pambanBridges.jfif',
            'Bandra-Worli Sea Link' => '/assets/images/Bandra-WorliSea.png',
        ];

        foreach ($bridges as $data) {
            $bridge = Infrastructure::create([
                ...$data,
                'type_id' => $bridgeType->id,
                'created_by' => $adminId,
                'image' => $bridgeImages[$data['name']] ?? '/assets/images/bridge-default.svg',
            ]);

            BridgeDetails::create([
                'infrastructure_id' => $bridge->id,
                'bridge_type' => $data['name'] === 'Howrah Bridge' ? 'Cantilever' : ($data['name'] === 'Pamban Bridge' ? 'Cantilever' : 'Cable-Stayed'),
                'length' => $data['name'] === 'Howrah Bridge' ? 705 : ($data['name'] === 'Pamban Bridge' ? 2065 : 560),
                'width' => $data['name'] === 'Howrah Bridge' ? 21.6 : ($data['name'] === 'Pamban Bridge' ? 6.6 : 20),
                'lanes' => $data['name'] === 'Howrah Bridge' ? 2 : ($data['name'] === 'Pamban Bridge' ? 2 : 8),
                'material' => 'Steel',
                'load_capacity' => $data['name'] === 'Howrah Bridge' ? '25 tonnes' : ($data['name'] === 'Pamban Bridge' ? '15 tonnes' : '40 tonnes'),
                'structural_condition' => $data['condition'],
                'surface_condition' => $data['condition'],
                'safety_status' => $data['condition'] === 'excellent' ? 'safe' : ($data['condition'] === 'good' ? 'safe' : 'caution'),
                'last_inspection_date' => now()->subMonths(rand(1, 6))->toDateString(),
            ]);
        }

        // Roads
        $roads = [
            [
                'name' => 'National Highway 48',
                'code' => 'RD-001',
                'location' => 'Delhi to Chennai',
                'construction_year' => 2010,
                'condition' => 'good',
                'description' => 'Major national highway connecting Delhi and Chennai via Jaipur, Ahmedabad, Mumbai, Pune and Bengaluru.',
            ],
            [
                'name' => 'East Coast Road',
                'code' => 'RD-002',
                'location' => 'Tamil Nadu',
                'construction_year' => 2002,
                'condition' => 'excellent',
                'description' => 'Two-lane highway along the Bay of Bengal coast from Chennai to Cuddalore via Pondicherry.',
            ],
            [
                'name' => 'Mumbai-Pune Expressway',
                'code' => 'RD-003',
                'location' => 'Maharashtra',
                'construction_year' => 2002,
                'condition' => 'excellent',
                'description' => 'India\'s first concrete, high-speed, six-lane concrete expressway.',
            ],
        ];

        $roadImages = [
            'NH-48 (Delhi-Mumbai Corridor)' => '/assets/images/NationalHighway48.jfif',
            'Coastal Highway (Mumbai-Goa)' => '/assets/images/coastal-highway-road.JPG',
            'Golden Quadrilateral (NH-19)' => '/assets/images/NationalHighway48.jfif',
        ];

        foreach ($roads as $data) {
            $road = Infrastructure::create([
                ...$data,
                'type_id' => $roadType->id,
                'created_by' => $adminId,
                'image' => $roadImages[$data['name']] ?? '/assets/images/road-default.svg',
            ]);

            RoadDetails::create([
                'infrastructure_id' => $road->id,
                'road_type' => $data['name'] === 'National Highway 48' ? 'National Highway' : ($data['name'] === 'East Coast Road' ? 'State Highway' : 'Expressway'),
                'start_point' => $data['name'] === 'National Highway 48' ? 'Delhi' : ($data['name'] === 'East Coast Road' ? 'Chennai' : 'Mumbai'),
                'end_point' => $data['name'] === 'National Highway 48' ? 'Chennai' : ($data['name'] === 'East Coast Road' ? 'Cuddalore' : 'Pune'),
                'length' => $data['name'] === 'National Highway 48' ? 2800 : ($data['name'] === 'East Coast Road' ? 120 : 94),
                'width' => $data['name'] === 'National Highway 48' ? 7.5 : ($data['name'] === 'East Coast Road' ? 7.0 : 25.0),
                'lanes' => $data['name'] === 'National Highway 48' ? 4 : ($data['name'] === 'East Coast Road' ? 2 : 6),
            ]);
        }

        // Buildings
        $buildings = [
            [
                'name' => 'Vidhana Soudha',
                'code' => 'BLD-001',
                'location' => 'Bengaluru, Karnataka',
                'address' => 'Dr. B. R. Ambedkar Veedhi, Bengaluru 560001',
                'construction_year' => 1956,
                'condition' => 'good',
                'description' => 'The seat of the state legislature of Karnataka. Constructed in the Neo-Dravidian style.',
            ],
            [
                'name' => 'Secretariat Building',
                'code' => 'BLD-002',
                'location' => 'New Delhi, Delhi',
                'address' => 'Raisina Hill, New Delhi 110001',
                'construction_year' => 1931,
                'condition' => 'fair',
                'description' => 'Houses the Cabinet Secretariat and administers the Government of India.',
            ],
        ];

        $buildingImages = [
            'Vidhana Soudha' => '/assets/images/VidhanaSoudha.png',
            'Secretariat Building' => '/assets/images/SecretariatBuilding.jpg',
        ];

        foreach ($buildings as $data) {
            $building = Infrastructure::create([
                ...$data,
                'type_id' => $buildingType->id,
                'created_by' => $adminId,
                'image' => $buildingImages[$data['name']] ?? '/assets/images/building-default.svg',
            ]);

            BuildingDetails::create([
                'infrastructure_id' => $building->id,
                'building_type' => $data['name'] === 'Vidhana Soudha' ? 'Government Building' : 'Government Building',
                'floors' => $data['name'] === 'Vidhana Soudha' ? 4 : 5,
                'area' => $data['name'] === 'Vidhana Soudha' ? 60000 : 45000,
                'department' => $data['name'] === 'Vidhana Soudha' ? 'State Legislature' : 'Cabinet Secretariat',
            ]);
        }

        // Hospitals
        $hospitals = [
            [
                'name' => 'All India Institute of Medical Sciences',
                'code' => 'HSP-001',
                'location' => 'New Delhi, Delhi',
                'address' => 'Ansari Nagar, New Delhi 110029',
                'construction_year' => 1956,
                'condition' => 'good',
                'description' => 'A public medical research university and hospital based in New Delhi.',
            ],
            [
                'name' => 'Apollo Hospital',
                'code' => 'HSP-002',
                'location' => 'Chennai, Tamil Nadu',
                'address' => 'Greams Rd, Thousand Lights, Chennai 600006',
                'construction_year' => 1983,
                'condition' => 'excellent',
                'description' => 'A multi-specialty tertiary care hospital owned by Apollo Hospitals Enterprise Limited.',
            ],
        ];

        $hospitalImages = [
            'All India Institute of Medical Sciences' => '/assets/images/AllMS.PNG',
            'Apollo Hospital' => '/assets/images/apollo-hospitals.webp',
        ];

        foreach ($hospitals as $data) {
            $hospital = Infrastructure::create([
                ...$data,
                'type_id' => $hospitalType->id,
                'created_by' => $adminId,
                'image' => $hospitalImages[$data['name']] ?? '/assets/images/hospital-default.svg',
            ]);

            HospitalDetails::create([
                'infrastructure_id' => $hospital->id,
                'hospital_type' => $data['name'] === 'All India Institute of Medical Sciences' ? 'Government Hospital' : 'Private Hospital',
                'beds' => $data['name'] === 'All India Institute of Medical Sciences' ? 2478 : 700,
                'emergency_phone' => $data['name'] === 'All India Institute of Medical Sciences' ? '011 2658 8500' : '044 2829 0290',
                'emergency_facility' => true,
                'departments' => $data['name'] === 'All India Institute of Medical Sciences' ? 'Cardiology, Neurology, Oncology, Pediatrics, Orthopedics' : 'Cardiology, Orthopedics, Neurology, Emergency Medicine',
                'email' => $data['name'] === 'All India Institute of Medical Sciences' ? 'info@aiims.edu' : 'info@apollohospitals.com',
                'website' => $data['name'] === 'All India Institute of Medical Sciences' ? 'https://aiims.edu' : 'https://www.apollohospitals.com',
            ]);
        }
    }
}
