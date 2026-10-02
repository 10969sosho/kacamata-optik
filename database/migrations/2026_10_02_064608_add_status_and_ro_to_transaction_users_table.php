<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_users', function (Blueprint $table) {
            $table->string('status')->default('ordered')->after('prescription_id');
            $table->string('ro1', 120)->nullable()->after('status')->comment('Nama yang periksa mata');
            $table->string('ro2', 120)->nullable()->after('ro1')->comment('Nama yang potong lensa');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'ro1', 'ro2']);
        });
    }
};
