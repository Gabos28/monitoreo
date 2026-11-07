<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Monolog\Handler\NullHandler;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'logging.channels.security' => [
                'driver' => 'monolog',
                'handler' => NullHandler::class,
            ],
        ]);
    }

    public function test_login_page_is_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertDontSee('Ver panel de monitoreo');
    }

    public function test_valid_credentials_authenticate_user_and_record_success(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'correct-password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'user@example.com',
            'password' => 'correct-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('login_attempts', [
            'identifier_hash' => hash('sha256', (string) $user->getAuthIdentifier()),
            'status' => 'success',
        ]);
    }

    public function test_invalid_credentials_return_an_error_and_record_failure(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'correct-password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseHas('login_attempts', [
            'identifier_hash' => hash('sha256', 'user@example.com'),
            'status' => 'failed',
        ]);
    }

    public function test_login_is_rejected_after_five_failed_attempts_from_the_same_ip(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => 'unknown@example.com',
                'password' => 'wrong-password',
            ])->assertRedirect();
        }

        $this->post(route('login.store'), [
            'email' => 'unknown@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);

        $this->assertDatabaseCount('login_attempts', 5);
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
