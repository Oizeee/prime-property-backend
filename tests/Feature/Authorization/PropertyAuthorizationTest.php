<?php

namespace Tests\Feature\Authorization;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithPrimeProperty;
use Tests\TestCase;

class PropertyAuthorizationTest extends TestCase
{
    use InteractsWithPrimeProperty;
    use RefreshDatabase;

    public function test_guest_cannot_access_internal_property_mutation_endpoints(): void
    {
        $property = Property::factory()->create();

        $this->postJson('/api/properties', $this->validPropertyPayload())
            ->assertUnauthorized();

        $this->putJson("/api/properties/{$property->id}", $this->validPropertyPayload())
            ->assertUnauthorized();

        $this->deleteJson("/api/properties/{$property->id}")
            ->assertUnauthorized();
    }

    public function test_admin_can_view_properties_and_apply_filters(): void
    {
        $admin = User::factory()->admin()->create();

        Property::factory()->create([
            'nama_property' => 'Ruko Filter Match',
            'tipe' => 'Ruko',
            'status' => 'in stock',
            'kawasan' => ['Krakatau'],
        ]);

        Property::factory()->create([
            'nama_property' => 'Villa Hidden',
            'tipe' => 'Villa',
            'status' => 'sold_out',
            'kawasan' => ['Pancing'],
        ]);

        $this->actingAs($admin)
            ->getJson('/api/properties?tipe=Ruko&status=in stock&kawasan[]=Krakatau')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.nama_property', 'Ruko Filter Match');
    }

    public function test_admin_cannot_create_update_or_delete_properties(): void
    {
        $admin = User::factory()->admin()->create();
        $property = Property::factory()->create();

        $this->actingAs($admin)
            ->postJson('/api/properties', $this->validPropertyPayload())
            ->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);

        $this->actingAs($admin)
            ->putJson("/api/properties/{$property->id}", $this->validPropertyPayload([
                'nama_property' => 'Updated By Admin',
            ]))
            ->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);

        $this->actingAs($admin)
            ->deleteJson("/api/properties/{$property->id}")
            ->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'nama_property' => $property->nama_property,
            'deleted_at' => null,
        ]);
    }

    public function test_superadmin_can_execute_full_property_crud(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $createResponse = $this->actingAs($superadmin)
            ->postJson('/api/properties', $this->validPropertyPayload([
                'nama_property' => 'Superadmin Created Ruko',
            ]));

        $createResponse->assertCreated()
            ->assertJsonPath('data.nama_property', 'Superadmin Created Ruko');

        $propertyId = $createResponse->json('data.id');

        $this->actingAs($superadmin)
            ->putJson("/api/properties/{$propertyId}", $this->validPropertyPayload([
                'nama_property' => 'Superadmin Updated Ruko',
                'price' => 2_000_000_000,
            ]))
            ->assertOk()
            ->assertJsonPath('data.nama_property', 'Superadmin Updated Ruko')
            ->assertJsonPath('data.price', 2_000_000_000);

        $this->assertDatabaseHas('audit_logs', [
            'property_id' => $propertyId,
            'user_id' => (string) $superadmin->id,
            'action' => 'updated',
        ]);

        $this->actingAs($superadmin)
            ->deleteJson("/api/properties/{$propertyId}")
            ->assertOk()
            ->assertJsonPath('message', 'Property berhasil dihapus.');

        $this->assertSoftDeleted('properties', ['id' => $propertyId]);
    }
}
