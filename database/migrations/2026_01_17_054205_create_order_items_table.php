<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Product snapshot (untuk preserve data saat product berubah/dihapus)
            $table->string('product_name');
            $table->string('product_slug');
            $table->string('product_image');
            $table->text('product_description')->nullable();

            // Pricing
            $table->decimal('price', 15, 2); // Price saat order
            $table->integer('quantity');
            $table->decimal('subtotal', 15, 2); // price * quantity

            // Product details snapshot
            $table->json('product_details')->nullable(); // Category, specs, etc

            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
