<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->string('sales_no');
            $table->integer('sortno')->comment('Urutan item per transaksi, mulai dari 1');
            $table->string('itemno')->comment('ID item (frame/lens/accessory) sebagai string');
            $table->string('item_type');
            $table->string('item_description');
            $table->string('username')->nullable()->comment('Nama pemakai kacamata');
            $table->integer('qty')->default(1);
            $table->string('unit')->default('pcs');
            $table->decimal('price', 12, 2);
            $table->decimal('disc_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->primary(['sales_no', 'sortno']);
            $table->foreign('sales_no')->references('sales_no')->on('transactions')->cascadeOnDelete();
            $table->index(['item_type', 'itemno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
