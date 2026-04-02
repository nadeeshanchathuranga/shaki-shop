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
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('colors')->nullOnDelete();
            $table->string('size')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->integer('rental_quantity')->default(0);
            $table->decimal('rent_price', 10, 2)->default(0);
            $table->decimal('commission_percentage_shop', 10, 2)->default(0);
            $table->decimal('commission_amount_shop', 10, 2)->default(0);
            $table->decimal('commission_percentage_supplier', 10, 2)->default(0);
            $table->decimal('commission_amount_supplier', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_items');
    }
};
