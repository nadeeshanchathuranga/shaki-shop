<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('late_fee', 10, 2)->default(0)->after('deposit');
            $table->integer('late_days')->default(0)->after('late_fee');
            $table->decimal('damage_amount', 10, 2)->default(0)->after('late_days');
            $table->decimal('deposit_refund', 10, 2)->default(0)->after('damage_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['late_fee', 'late_days', 'damage_amount', 'deposit_refund']);
        });
    }
};
