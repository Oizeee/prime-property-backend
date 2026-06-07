<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /** Contoh nama properti sesuai dokumen Prime Property. */
    private const PROPERTY_NAMES = [
        'Aston Villas',
        'Banyan Tree',
        'Green Lake Residence',
        'Helvetia Park',
        'Krakatau Square',
        'Pancing Hills',
        'Cemara Asri Estate',
        'Sunrise Ruko',
        'Taman Seruni',
        'Villa Permata',
        'Cluster Dahlia',
        'Ruko Medan Fair',
    ];

    /** Kawasan / area tagging untuk filter multi-select. */
    private const KAWASAN_OPTIONS = [
        'Krakatau',
        'Pancing',
        'Cemara Asri',
        'Helvetia',
        'Medan Johor',
        'Sunggal',
        'Tanjung Gusta',
    ];

    /** Grup cluster opsional. */
    private const GROUP_OPTIONS = [
        'Cluster A',
        'Cluster B',
        'Phase 1',
        'Phase 2',
        'Blok Utara',
        'Blok Selatan',
        null,
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipe = fake()->randomElement(Property::TIPE_OPTIONS);
        $kawasanCount = fake()->numberBetween(1, 2);
        $hadapCount = fake()->numberBetween(1, 2);

        return [
            'nama_property' => fake()->randomElement(self::PROPERTY_NAMES).' '.fake()->bothify('##'),
            'group' => fake()->randomElement(self::GROUP_OPTIONS),
            'lebar' => fake()->randomFloat(2, 4, 25),
            'panjang' => fake()->randomFloat(2, 8, 45),
            'hadap' => fake()->randomElements(Property::HADAP_OPTIONS, $hadapCount),
            'tipe' => $tipe,
            'tingkat' => fake()->randomFloat(1, 1, 4),
            // Harga Rupiah penuh (integer), bukan float — contoh: 1350000000
            'price' => fake()->numberBetween(350_000_000, 8_500_000_000),
            'carport' => fake()->boolean(45),
            'status' => fake()->randomElement(Property::STATUS_OPTIONS),
            'siap' => fake()->randomElement(Property::SIAP_OPTIONS),
            'maps_link' => 'https://www.google.com/maps/place/'.urlencode(
                fake()->randomElement(self::KAWASAN_OPTIONS)
            ).'/@'.fake()->latitude().','.fake()->longitude().',17z',
            'kawasan' => fake()->randomElements(self::KAWASAN_OPTIONS, $kawasanCount),
            'unit' => fake()->optional(0.7)->bothify(strtoupper(substr($tipe, 0, 1)).'-##'),
            'created_by' => '1',
        ];
    }

    /**
     * Tipe Ruko.
     */
    public function ruko(): static
    {
        return $this->state(fn () => [
            'tipe' => 'Ruko',
            'tingkat' => fake()->randomFloat(1, 1, 3),
        ]);
    }

    /**
     * Tipe Villa.
     */
    public function villa(): static
    {
        return $this->state(fn () => [
            'tipe' => 'Villa',
            'tingkat' => fake()->randomFloat(1, 1, 2),
        ]);
    }

    /**
     * Status tersedia (in stock).
     */
    public function inStock(): static
    {
        return $this->state(fn () => [
            'status' => 'in stock',
        ]);
    }

    /**
     * Status habis terjual.
     */
    public function soldOut(): static
    {
        return $this->state(fn () => [
            'status' => 'sold_out',
        ]);
    }
}
