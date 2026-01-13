<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keycaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('profile', ['Cherry', 'OEM', 'SA', 'XDA', 'ASA', 'MT3', 'DSA', 'KAT']);
            $table->enum('material', ['ABS', 'PBT', 'POM']);
            $table->enum('printing_method', ['Double-shot', 'Dye-sub', 'Laser', 'Pad Print']);
            $table->integer('key_count')->default(104);
            $table->string('color_scheme')->nullable(); // e.g., "White on Black", "Beige/Green"
            $table->timestamps();

            $table->index('profile');
            $table->index('material');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keycaps');
    }
};
