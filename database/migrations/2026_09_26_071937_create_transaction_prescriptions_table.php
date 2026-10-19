<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_prescriptions', function (Blueprint $table) {
            $table->string('sales_no', 64);
            $table->integer('sortno')->comment('Merujuk sortno di transaction_items');
            $table->string('itemno_lensa')->nullable();
            $table->string('item_type')->nullable();
            $table->integer('qty')->default(1);
            $table->string('sisi', 5)->comment('R = kanan (OD), L = kiri (OS)');
            $table->string('sph')->nullable();
            $table->string('cyl')->nullable();
            $table->string('axis')->nullable();
            $table->string('add')->nullable();
            $table->string('pd')->nullable();
            $table->string('ro1')->nullable()->comment('Nama yang periksa mata');
            $table->string('ro2')->nullable()->comment('Nama yang potong lensa');
            $table->string('resep')->nullable()->comment('Nama dokter / optometris');
            $table->string('jenis_lensa')->nullable();
            $table->text('ket')->nullable();
            $table->timestamps();

            $table->primary(['sales_no', 'sortno', 'sisi']);
            $table->foreign('sales_no')->references('sales_no')->on('transactions')->cascadeOnDelete();
            $table->index('sortno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_prescriptions');
    }
};
