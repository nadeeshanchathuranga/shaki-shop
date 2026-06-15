<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_commission_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_commission_id')->constrained('event_commissions')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_commission_payments');
    }
};
