<?php

namespace Tests\Feature;

use App\Models\Infrastructure;
use App\Models\MaintenanceRequest;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportIssueFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_report_form_contains_csrf_token(): void
    {
        $this->get('/report-issue')
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_submitting_report_without_csrf_token_returns_419(): void
    {
        // Laravel's CSRF middleware skips verification while the app environment is
        // "testing" (runningUnitTests() bypass). Switch the environment so the request
        // goes through real verification, exactly like a browser request would.
        $this->app->detectEnvironment(fn () => 'local');

        $this->post('/report-issue', $this->validPayload())->assertStatus(419);
    }

    public function test_successful_submission_redirects_to_confirmation_page(): void
    {
        $payload = $this->validPayload();

        $response = $this->post('/report-issue', $payload + ['_token' => csrf_token()]);

        $report = MaintenanceRequest::query()
            ->where('title', $payload['title'])
            ->first();

        $this->assertNotNull($report, 'The report should be persisted after submission.');
        $this->assertSame('pending', $report->status);
        $this->assertNotEmpty($report->request_number);

        $response->assertRedirect(route('public.report.success', $report->request_number));
        $response->assertSessionHas('success');

        // The confirmation page shows the tracking code, current status and a track option.
        $this->get(route('public.report.success', $report->request_number))
            ->assertOk()
            ->assertSee($report->request_number)
            ->assertSee('Pending')
            ->assertSee(route('public.track.show', $report->request_number), false);
    }

    public function test_invalid_submission_redirects_back_with_errors_and_no_record(): void
    {
        $before = MaintenanceRequest::count();

        $response = $this->from('/report-issue')->post('/report-issue', [
            '_token' => csrf_token(),
            'infrastructure_type' => 'bridge',
            'infrastructure_id' => '',
            'title' => '',
            'description' => '',
            'reporter_name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertRedirect('/report-issue');
        $response->assertSessionHasErrors([
            'infrastructure_id',
            'title',
            'description',
            'reporter_name',
            'email',
        ]);
        $this->assertSame($before, MaintenanceRequest::count());
    }

    public function test_confirmation_page_404s_for_unknown_tracking_code(): void
    {
        $this->get('/report-issue/confirmation/NOPE-9999-999999')->assertNotFound();
    }

    public function test_confirmation_page_loads_for_seeded_report(): void
    {
        $number = MaintenanceRequest::query()->value('request_number');
        $this->assertNotNull($number);

        $this->get('/report-issue/confirmation/' . $number)->assertOk();
    }

    private function validPayload(): array
    {
        $infrastructure = Infrastructure::with('type')->firstOrFail();

        return [
            'infrastructure_type' => $infrastructure->type->slug,
            'infrastructure_id' => $infrastructure->id,
            'title' => 'QA test: cracked pillar near east approach',
            'description' => 'A visible crack runs down the support pillar.',
            'location' => 'East approach, marker 12',
            'reporter_name' => 'Test Citizen',
            'email' => 'citizen@example.com',
            'phone' => '9876543210',
        ];
    }
}
