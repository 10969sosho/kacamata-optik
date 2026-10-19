<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('sales_no')->primary();
            $table->date('sales_date');
            $table->time('sales_time');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('input_by')->comment('Nama user (kasir) yang input transaksi');
            $table->string('payment_by')->nullable()->comment('Metode pembayaran: cash, transfer, qris, card');
            $table->string('edc_id')->nullable();
            $table->decimal('grandtotal', 12, 2);
            $table->text('sales_notes')->nullable();
            $table->string('sales_status')->default('ordered');
            $table->timestamps();

            $table->index('sales_date');
            $table->index('sales_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
