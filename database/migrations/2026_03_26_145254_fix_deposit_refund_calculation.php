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
        // Fix deposit_refund calculation for existing returned rentals
        // deposit_refund should be: deposit - (late_fee + damage_amount)
        DB::statement('
            UPDATE sales 
            SET deposit_refund = COALESCE(deposit, 0) - (COALESCE(late_fee, 0) + COALESCE(damage_amount, 0))
            WHERE is_rental_returned = 1 AND deposit_refund = 0 AND deposit > 0
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to 0 for all deposit_refund on returned rentals
        DB::statement('
            UPDATE sales 
            SET deposit_refund = 0
            WHERE is_rental_returned = 1
        ');
    }
};
