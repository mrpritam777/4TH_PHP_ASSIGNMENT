<?php

namespace Tests\Feature;

use App\Models\MaintenanceRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfraCareSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_load(): void
    {
        $response = $this->get('/');
        if ($response->status() !== 200) {
            fwrite(STDERR, "\nSTATUS: " . $response->status() . "\n");
            if ($response->exception) {
                fwrite(STDERR, "EXCEPTION: " . get_class($response->exception) . ': ' . $response->exception->getMessage() . "\n");
                fwrite(STDERR, "AT: " . $response->exception->getFile() . ':' . $response->exception->getLine() . "\n");
            }
        }
        $response->assertOk();
        $this->get('/infrastructure')->assertOk();
        $this->get('/report-issue')->assertOk();
        $this->get('/track')->assertOk();
        $this->get('/login')->assertOk();

        $bridge = MaintenanceRequest::query()->first();
        $this->assertNotNull($bridge);

        $firstBridge = \App\Models\Infrastructure::ofType('bridge')->first();
        $this->assertNotNull($firstBridge);
        $this->get('/bridge/' . $firstBridge->id)->assertOk();

        $number = MaintenanceRequest::query()->value('request_number');
        $this->get('/track/' . $number)->assertOk();
    }

    public function test_admin_pages_load_after_login(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);
        $this->actingAs($admin);

        $this->get('/admin/dashboard')->assertOk();
        $this->get('/admin/bridges')->assertOk();
        $this->get('/admin/roads')->assertOk();
        $this->get('/admin/buildings')->assertOk();
        $this->get('/admin/hospitals')->assertOk();
        $this->get('/admin/infrastructures')->assertOk();
        $this->get('/admin/maintenance')->assertOk();
        $this->get('/admin/maintenance/history')->assertOk();
        $this->get('/admin/staff')->assertOk();
        $this->get('/admin/contacts')->assertOk();
        $this->get('/admin/reports/infrastructure')->assertOk();
        $this->get('/admin/reports/maintenance')->assertOk();
        $this->get('/admin/reports/cost')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/settings')->assertOk();
        $this->get('/admin/activity-logs')->assertOk();
    }

    public function test_staff_pages_load_after_login(): void
    {
        $staff = User::where('role', 'staff')->first();
        $this->assertNotNull($staff);
        $this->actingAs($staff);

        $this->get('/staff/tasks')->assertOk();
    }

    public function test_guests_cannot_access_protected_areas(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/staff/tasks')->assertRedirect('/login');
    }
}
