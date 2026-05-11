<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_items', function (Blueprint $table) {
            $table->integer('total_quantity')->default(0)->after('rental_quantity');
        });

        // Seed total_quantity from current rental_quantity for existing items
        DB::statement('UPDATE rental_items SET total_quantity = rental_quantity');
    }

    public function down(): void
    {
        Schema::table('rental_items', function (Blueprint $table) {
            $table->dropColumn('total_quantity');
        });
    }
};
