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
        Schema::table('rental_items', function (Blueprint $table) {
            $table->string('commission_type_shop', 10)->default('percentage')->after('rent_price');
            $table->string('commission_type_supplier', 10)->default('percentage')->after('commission_amount_shop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_items', function (Blueprint $table) {
            $table->dropColumn(['commission_type_shop', 'commission_type_supplier']);
        });
    }
};
