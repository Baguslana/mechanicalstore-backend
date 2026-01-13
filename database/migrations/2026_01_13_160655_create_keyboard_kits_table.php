<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyboard_kits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('size', ['60%', '65%', '75%', 'TKL', 'Full Size', '40%', 'Alice']);
            $table->string('case_material')->nullable(); // Aluminum, Acrylic, Polycarbonate, etc
            $table->string('mount_type')->nullable(); // Gasket, Top, Tray, O-ring, etc
            $table->enum('pcb_type', ['Hot-swap', 'Solder', 'Both'])->default('Hot-swap');
            $table->boolean('has_rotary_encoder')->default(false);
            $table->string('layout')->nullable(); // ANSI, ISO, JIS
            $table->timestamps();

            $table->index('size');
            $table->index('pcb_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyboard_kits');
    }
};
