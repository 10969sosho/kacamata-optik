<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('sales_no');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->string('reference_number')->nullable();
            $table->dateTime('paid_at');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('sales_no')->references('sales_no')->on('transactions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
