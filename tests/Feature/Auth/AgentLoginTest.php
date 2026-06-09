<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithPrimeProperty;
use Tests\TestCase;

class AgentLoginTest extends TestCase
{
    use InteractsWithPrimeProperty;
    use RefreshDatabase;

    public function test_agent_can_login_with_valid_credentials_and_receive_token(): void
    {
        $user = User::factory()->superadmin()->create([
            'email' => 'agent@primeproperty.test',
            'password' => 'SecretPass123',
        ]);

        $this->clearLoginRateLimiter($user->email);

        $response = $this->postJson('/api/agent/login', [
            'email' => $user->email,
            'password' => 'SecretPass123',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login berhasil.')
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.role', User::ROLE_SUPERADMIN)
            ->assertJsonStructure(['token', 'token_type']);

        $token = $response->json('token');

        // Test fetching active user via /api/me using the token.
        $this->getJson('/api/me', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertOk()
            ->assertJsonPath('user.email', $user->email);

        // Test logout invalidates the token.
        $this->postJson('/api/agent/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertOk()
            ->assertJsonPath('message', 'Logout berhasil.');

        // Forget guards in the test container to force re-authentication for the next request.
        \Illuminate\Support\Facades\Auth::forgetGuards();

        // Test the token is now invalid.
        $this->getJson('/api/me', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertUnauthorized();
    }

    public function test_login_locks_account_after_five_failed_attempts(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'locked@primeproperty.test',
            'password' => 'CorrectPassword1',
        ]);

        $this->clearLoginRateLimiter($user->email);

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $this->postJson('/api/agent/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnprocessable()
                ->assertJsonPath('message', 'Email atau password salah.');
        }

        // Fifth failed attempt triggers lockout (429).
        $this->postJson('/api/agent/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(429)
            ->assertJsonPath('message', 'Akun terkunci karena terlalu banyak percobaan login gagal.');

        // Subsequent attempts remain blocked while lockout is active.
        $this->postJson('/api/agent/login', [
            'email' => $user->email,
            'password' => 'CorrectPassword1',
        ])->assertStatus(429)
            ->assertJsonPath('message', 'Akun terkunci karena terlalu banyak percobaan login gagal.');

        $this->assertGuest();
    }
}
