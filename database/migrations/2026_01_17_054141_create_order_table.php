<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g., ORD-20240117-001

            // Customer Information
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');

            // Shipping Address
            $table->text('shipping_address');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code');

            // Order Details
            $table->decimal('subtotal', 15, 2);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);

            // Payment
            $table->enum('payment_method', ['bank_transfer', 'e_wallet', 'credit_card', 'cod'])->default('bank_transfer');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->string('payment_proof')->nullable(); // Upload bukti transfer
            $table->timestamp('paid_at')->nullable();

            // Order Status
            $table->enum('status', [
                'pending',           // Menunggu pembayaran
                'processing',        // Sedang diproses
                'shipped',          // Dikirim
                'delivered',        // Terkirim
                'cancelled',        // Dibatalkan
                'refunded'          // Dikembalikan
            ])->default('pending');

            // Midtrans Integration
            $table->string('midtrans_order_id')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_payment_type')->nullable();
            $table->text('midtrans_response')->nullable(); // JSON response

            // Tracking
            $table->string('shipping_courier')->nullable(); // JNE, TIKI, etc
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('order_number');
            $table->index('customer_email');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
