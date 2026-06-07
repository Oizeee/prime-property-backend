<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the properties table for Prime Property listings.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('nama_property', 100)->index();
            $table->string('group')->nullable()->index();
            $table->decimal('lebar', 10, 2);
            $table->decimal('panjang', 10, 2);
            $table->json('hadap');
            $table->string('tipe', 20);
            $table->decimal('tingkat', 3, 1);
            $table->unsignedBigInteger('price')->index();
            $table->boolean('carport')->default(false);
            $table->string('status', 20)->default('in stock')->index();
            $table->string('siap', 30);
            $table->text('maps_link')->nullable();
            $table->json('kawasan');
            $table->string('unit')->nullable();
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
