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
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('deposit', 10, 2)->default(0)->after('advance_amount');
            $table->string('rental_type')->nullable()->after('deposit'); // 'rent_now', 'rent_later', or null
            $table->unsignedBigInteger('booking_id')->nullable()->after('rental_type');
            $table->foreign('booking_id')->references('id')->on('rental_bookings')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropColumn(['deposit', 'rental_type', 'booking_id']);
        });
    }
};
