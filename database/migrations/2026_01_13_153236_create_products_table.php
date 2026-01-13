<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 15, 2);
            $table->text('image');
            $table->text('description');
            $table->boolean('in_stock')->default(true);
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();

            $table->index('category_id');
            $table->index('in_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
