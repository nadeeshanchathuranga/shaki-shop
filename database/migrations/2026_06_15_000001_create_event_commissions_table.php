<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_commissions')) {
            Schema::create('event_commissions', function (Blueprint $table) {
                $table->id();
                $table->string('event_title');
                $table->date('event_date');
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->string('customer_name');
                $table->string('customer_email')->nullable();
                $table->string('customer_phone');
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->decimal('advance_amount', 15, 2)->default(0);
                $table->enum('payment_status', ['pending', 'partially_paid', 'paid'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_commissions');
    }
};
