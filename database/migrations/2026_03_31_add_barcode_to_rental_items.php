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
        if (!Schema::hasColumn('rental_items', 'barcode')) {
            Schema::table('rental_items', function (Blueprint $table) {
                $table->string('barcode')->nullable()->after('item_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('rental_items', 'barcode')) {
            Schema::table('rental_items', function (Blueprint $table) {
                $table->dropColumn('barcode');
            });
        }
    }
};
