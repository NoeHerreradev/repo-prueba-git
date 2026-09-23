<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricsReportTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_authenticated_user_can_download_monthly_metrics_pdf(): void
    {
        $admin = User::where('role', UserRole::Admin)->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.monthly-metrics', [
                'month' => 9,
                'year' => 2026,
            ]));

        $response->assertSuccessful();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_unauthenticated_user_cannot_download_report(): void
    {
        $response = $this->get(route('admin.reports.monthly-metrics', [
            'month' => 9,
            'year' => 2026,
        ]));

        $response->assertRedirect('/admin/login');
    }
}
