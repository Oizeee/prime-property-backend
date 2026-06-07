<?php

namespace Tests\Feature\Contact;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithPrimeProperty;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use InteractsWithPrimeProperty;
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validContactPayload(): array
    {
        return [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'nomor_hp' => '081234567890',
            'pesan' => 'Saya tertarik dengan properti Prime Property.',
        ];
    }

    public function test_contact_form_accepts_valid_submission(): void
    {
        $this->clearContactRateLimiter();

        $this->postJson('/api/contact', $this->validContactPayload())
            ->assertCreated()
            ->assertJsonPath(
                'message',
                'Pesan Anda telah terkirim. Tim kami akan segera menghubungi Anda.',
            );
    }

    public function test_contact_form_is_throttled_after_three_submissions_per_hour(): void
    {
        $this->clearContactRateLimiter();

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->postJson('/api/contact', $this->validContactPayload([
                'email' => "user{$attempt}@example.com",
            ]))->assertCreated();
        }

        $this->postJson('/api/contact', $this->validContactPayload([
            'email' => 'fourth@example.com',
        ]))->assertStatus(429);
    }

    public function test_contact_form_rejects_invalid_payload(): void
    {
        $this->clearContactRateLimiter();

        $this->postJson('/api/contact', [
            'nama' => '',
            'email' => 'not-an-email',
            'nomor_hp' => '123',
            'pesan' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nama', 'email', 'nomor_hp', 'pesan']);
    }
}
