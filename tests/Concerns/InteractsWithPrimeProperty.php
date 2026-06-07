<?php

namespace Tests\Concerns;

use App\Models\Property;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Shared helpers for Prime Property feature tests.
 */
trait InteractsWithPrimeProperty
{
    /**
     * Valid property payload for create/update requests.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validPropertyPayload(array $overrides = []): array
    {
        return array_merge([
            'nama_property' => 'Ruko Prime Krakatau',
            'group' => 'Cluster A',
            'lebar' => 5.5,
            'panjang' => 12.0,
            'hadap' => ['Utara', 'Timur'],
            'tipe' => 'Ruko',
            'tingkat' => 2.0,
            'price' => 1_500_000_000,
            'carport' => true,
            'status' => 'in stock',
            'siap' => 'siap_huni',
            'maps_link' => 'https://maps.google.com/maps?q=prime-property',
            'kawasan' => ['Krakatau', 'Pancing'],
            'unit' => 'A-01',
        ], $overrides);
    }

    /**
     * Clear login attempt / lockout counters for an email address.
     */
    protected function clearLoginRateLimiter(string $email): void
    {
        $normalized = Str::lower($email);

        RateLimiter::clear('agent-login-attempts:'.sha1($normalized));
        RateLimiter::clear('agent-login-lockout:'.sha1($normalized));
    }

    /**
     * Clear the contact-form throttle bucket for the current test IP.
     */
    protected function clearContactRateLimiter(string $ip = '127.0.0.1'): void
    {
        RateLimiter::clear('contact:'.$ip);
    }

    /**
     * Simulate a first-party SPA request so Sanctum enables session cookies.
     */
    protected function withStatefulApi(): static
    {
        return $this->withHeaders([
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost',
            'Accept' => 'application/json',
        ]);
    }

    /**
     * Prime CSRF protection for stateful Sanctum requests in tests.
     */
    protected function obtainCsrfCookie(): static
    {
        $this->get('/sanctum/csrf-cookie')->assertNoContent();

        return $this;
    }
}
