<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('switches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('switch_type', ['Linear', 'Tactile', 'Clicky']);
            $table->string('actuation_force')->nullable(); // e.g., "50g"
            $table->string('travel_distance')->nullable(); // e.g., "4mm"
            $table->integer('quantity_per_pack')->default(70);
            $table->boolean('is_factory_lubed')->default(false);
            $table->string('housing_material')->nullable(); // Nylon, Polycarbonate, etc
            $table->string('stem_material')->nullable(); // POM, etc
            $table->timestamps();

            $table->index('switch_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('switches');
    }
};
