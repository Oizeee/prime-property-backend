<?php

namespace Tests\Feature\Property;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_property_soft_deletes_record_and_hides_it_from_default_listing(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $property = Property::factory()->create([
            'nama_property' => 'Soft Delete Target',
        ]);

        Property::factory()->create([
            'nama_property' => 'Visible Listing',
        ]);

        $this->actingAs($superadmin)
            ->deleteJson("/api/properties/{$property->id}")
            ->assertOk();

        $this->assertSoftDeleted('properties', ['id' => $property->id]);
        $this->assertNotNull(Property::withTrashed()->find($property->id)?->deleted_at);

        $listingResponse = $this->getJson('/api/properties');

        $listingResponse->assertOk();

        $names = collect($listingResponse->json('data'))->pluck('nama_property');

        $this->assertFalse($names->contains('Soft Delete Target'));
        $this->assertTrue($names->contains('Visible Listing'));
    }
}
