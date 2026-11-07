<?php

namespace Tests\Feature;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_button_to_open_monitoring_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ver panel de monitoreo')
            ->assertSee(route('monitor.index'));
    }

    public function test_guest_cannot_open_monitoring_panel_or_metrics_api(): void
    {
        $this->get(route('monitor.index'))
            ->assertRedirect(route('login'));

        $this->getJson('/api/metrics')
            ->assertUnauthorized();

        $this->getJson('/api/login-attempts')
            ->assertUnauthorized();
    }

    public function test_monitoring_panel_lists_only_recent_failed_or_blocked_login_attempts(): void
    {
        $user = User::factory()->create();
        LoginAttempt::create([
            'identifier_hash' => hash('sha256', 'user@example.com'),
            'ip_address' => '192.0.2.10',
            'status' => 'failed',
            'user_agent' => 'Test browser',
            'created_at' => now()->subMinute(),
        ]);
        LoginAttempt::create([
            'ip_address' => '192.0.2.11',
            'status' => 'blocked',
            'user_agent' => 'Test browser',
            'created_at' => now(),
        ]);
        LoginAttempt::create([
            'identifier_hash' => hash('sha256', 'user-id'),
            'ip_address' => '192.0.2.12',
            'status' => 'success',
            'user_agent' => 'Test browser',
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('monitor.index'))
            ->assertOk()
            ->assertSee('Intentos fallidos de inicio de sesión')
            ->assertSee('id="refresh-login-attempts" type="button">Actualizar lista', false)
            ->assertSee(
                'data-metrics-url="'.$this->app['request']->getSchemeAndHttpHost().'/api/metrics"',
                false,
            )
            ->assertSee(
                'data-login-attempts-url="'.$this->app['request']->getSchemeAndHttpHost().'/api/login-attempts"',
                false,
            )
            ->assertSee('192.0.2.10')
            ->assertSee('192.0.2.11')
            ->assertDontSee('192.0.2.12');

        $this->getJson('/api/metrics')
            ->assertOk()
            ->assertJsonPath('login_attempts.0.ip_address', '192.0.2.11')
            ->assertJsonPath('login_attempts.1.ip_address', '192.0.2.10');

        $this->getJson('/api/login-attempts')
            ->assertOk()
            ->assertJsonPath('login_attempts.0.ip_address', '192.0.2.11')
            ->assertJsonPath('login_attempts.1.ip_address', '192.0.2.10');
    }
}
