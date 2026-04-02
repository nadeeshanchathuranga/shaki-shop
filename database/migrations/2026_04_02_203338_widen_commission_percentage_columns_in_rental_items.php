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
            $table->decimal('commission_percentage_shop', 10, 2)->default(0)->change();
            $table->decimal('commission_percentage_supplier', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_items', function (Blueprint $table) {
            $table->decimal('commission_percentage_shop', 5, 2)->default(0)->change();
            $table->decimal('commission_percentage_supplier', 5, 2)->default(0)->change();
        });
    }
};
