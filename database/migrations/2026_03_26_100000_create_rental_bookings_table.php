<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rental_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_order_id')->unique();
            $table->string('customer_name');
            $table->foreignId('rental_item_id')->nullable()->constrained('rental_items')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->date('rental_date_from')->nullable();
            $table->date('rental_date_to')->nullable();
            $table->decimal('advance_amount', 10, 2)->default(0);
            $table->string('status')->default('booked'); // 'booked' or 'completed'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_bookings');
    }
};
