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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('inv_id');
            $table->string('type');
            $table->string('quantity_before');
            $table->string('quantity');
            $table->string('quantity_after');
            $table->string('amount')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->decimal('unit_price', 12, 4)->nullable();
            $table->integer('exp_type')->default(0);
            $table->string('unit')->nullable();
            $table->string('remarks');
            $table->string('user_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('is_paid')->default('1');
            $table->integer('payment_class')->nullable();
            $table->string('store_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
