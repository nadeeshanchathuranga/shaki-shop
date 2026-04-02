<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('rental_items')) {
            DB::statement('ALTER TABLE `rental_items` MODIFY `commission_percentage_shop` DECIMAL(10,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE `rental_items` MODIFY `commission_percentage_supplier` DECIMAL(10,2) NOT NULL DEFAULT 0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rental_items')) {
            DB::statement('ALTER TABLE `rental_items` MODIFY `commission_percentage_shop` DECIMAL(5,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE `rental_items` MODIFY `commission_percentage_supplier` DECIMAL(5,2) NOT NULL DEFAULT 0');
        }
    }
};
