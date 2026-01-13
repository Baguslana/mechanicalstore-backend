<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('accessory_type', ['Lube', 'Stabilizer', 'Film', 'Tool', 'Cable', 'Foam', 'Other']);
            $table->string('quantity')->nullable(); // e.g., "5ml", "110pcs", "1 set"
            $table->string('size_compatibility')->nullable(); // e.g., "65%", "Universal"
            $table->string('variant')->nullable(); // Color, material variant
            $table->timestamps();

            $table->index('accessory_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessories');
    }
};
