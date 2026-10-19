<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_prescriptions', function (Blueprint $table) {
            $table->string('source', 20)->nullable()->after('jenis_lensa')
                ->comment('Sumber resep: in_store (periksa di toko) / bring_own (bawa resep sendiri)');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_prescriptions', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
