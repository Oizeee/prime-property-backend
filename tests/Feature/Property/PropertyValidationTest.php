<?php

namespace Tests\Feature\Property;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithPrimeProperty;
use Tests\TestCase;

class PropertyValidationTest extends TestCase
{
    use InteractsWithPrimeProperty;
    use RefreshDatabase;

    public function test_property_creation_fails_when_validation_rules_are_violated(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)
            ->postJson('/api/properties', $this->validPropertyPayload([
                'nama_property' => 'AB',
                'price' => 0,
                'maps_link' => 'https://example.com/not-google-maps',
            ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nama_property',
                'price',
                'maps_link',
            ]);
    }

    public function test_property_creation_fails_when_price_is_negative(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->postJson('/api/properties', $this->validPropertyPayload([
                'price' => -100,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price']);
    }

    public function test_property_creation_succeeds_with_valid_google_maps_url(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->postJson('/api/properties', $this->validPropertyPayload([
                'maps_link' => 'https://lh5.googleusercontent.com/maps/test',
            ]))
            ->assertCreated()
            ->assertJsonPath('data.maps_link', 'https://lh5.googleusercontent.com/maps/test');
    }
}
