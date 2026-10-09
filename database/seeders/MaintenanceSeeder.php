<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MaintenanceRequest;
use App\Models\Infrastructure;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $infrastructures = Infrastructure::all();
        $admins = User::where('role', 'admin')->get();
        $staff = User::where('role', 'staff')->get();

        $requestTypes = [
            'Pothole repair needed on surface',
            'Crack expansion in girder section',
            'Rust detection on support columns',
            'Debris accumulation in drainage system',
            'Lighting failure on span lights',
            'Joint sealant deterioration',
            'Vegetation growth affecting foundation',
            'Paint peeling due to weather exposure',
            'Sensor malfunction in monitoring system',
            'Barrier damage from vehicle impact',
        ];

        $priorities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['pending', 'under_review', 'assigned', 'in_progress', 'completed', 'cancelled'];

        $counter = 1;
        foreach ($infrastructures as $infra) {
            // Create 1-3 requests per infrastructure
            $requestCount = rand(1, 3);
            for ($i = 0; $i < $requestCount; $i++) {
                $priority = $priorities[array_rand($priorities)];
                $status = $statuses[array_rand($statuses)];
                $reporter = $admins->random();
                $assigned = $status === 'assigned' || $status === 'in_progress' || $status === 'completed' ? $staff->random() : null;

                $created = Carbon::now()->subDays(rand(1, 90));
                $start = $created->addDays(rand(0, 5));
                $expected = $start->addDays(rand(7, 30));
                $completed = $status === 'completed' ? $expected->addDays(rand(-2, 2)) : null;

                $requestNumber = $this->generateRequestNumber($infra->type->slug, $counter++);

                $maintenance = MaintenanceRequest::create([
                    'request_number' => $requestNumber,
                    'infrastructure_id' => $infra->id,
                    'reported_by' => $reporter->id,
                    'assigned_to' => $assigned ? $assigned->id : null,
                    'title' => $requestTypes[array_rand($requestTypes)] . ' - ' . $infra->name,
                    'description' => 'During routine inspection, it was observed that ' . strtolower($requestTypes[array_rand($requestTypes)]) . '. Immediate attention is required to prevent further deterioration and ensure safety.',
                    'priority' => $priority,
                    'status' => $status,
                    'estimated_cost' => rand(5000, 500000),
                    'actual_cost' => $status === 'completed' ? rand(4000, 450000) : null,
                    'start_date' => $status !== 'pending' && $status !== 'under_review' ? $start->toDateString() : null,
                    'expected_completion_date' => $status !== 'pending' && $status !== 'under_review' ? $expected->toDateString() : null,
                    'completion_date' => $completed ? $completed->toDateString() : null,
                    'before_image' => rand(0, 1) ? 'maintenance/before/sample1.jpg' : null,
                    'after_image' => $status === 'completed' && rand(0, 1) ? 'maintenance/after/sample1.jpg' : null,
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);

                // Add 0-3 updates
                $updateCount = rand(0, 3);
                $currentStatus = 'pending';
                for ($j = 0; $j < $updateCount; $j++) {
                    $updateDate = $maintenance->created_at->addDays(rand(1, 15))->addHours(rand(0, 23));
                    $newStatus = [$currentStatus, 'assigned', 'in_progress', 'on_hold'][array_rand([$currentStatus, 'assigned', 'in_progress', 'on_hold'])];
                    $newStatus = in_array($newStatus, ['pending', 'under_review', 'assigned', 'in_progress', 'on_hold', 'completed', 'cancelled']) ? $newStatus : $currentStatus;

                    $maintenance->updates()->create([
                        'maintenance_request_id' => $maintenance->id,
                        'updated_by' => $assigned ? $assigned->id : $reporter->id,
                        'status' => $newStatus,
                        'notes' => 'Work progressed as planned. Team addressed ' . ($j === 0 ? 'initial assessment' : ($j === 1 ? 'material procurement' : 'final finishing')) . '.',
                        'image' => rand(0, 1) ? 'maintenance/updates/sample' . rand(1, 3) . '.jpg' : null,
                        'created_at' => $updateDate,
                        'updated_at' => $updateDate,
                    ]);

                    $currentStatus = $newStatus;
                }

                // If status changed via updates, reflect it in the main request (optional)
                if ($currentStatus !== $maintenance->status && in_array($currentStatus, ['completed', 'cancelled'])) {
                    $maintenance->update([
                        'status' => $currentStatus,
                        'actual_cost' => $currentStatus === 'completed' ? $maintenance->actual_cost ?? rand(4000, 450000) : $maintenance->actual_cost,
                        'completion_date' => $currentStatus === 'completed' ? ($maintenance->completion_date ?? $updateDate->toDateString()) : $maintenance->completion_date,
                    ]);
                }
            }
        }
    }

    private function generateRequestNumber(string $slug, int $sequence): string
    {
        $prefix = strtoupper(substr($slug, 0, 3));
        $year = now()->year;
        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }
}
