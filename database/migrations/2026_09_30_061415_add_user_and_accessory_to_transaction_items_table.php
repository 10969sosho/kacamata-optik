<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreignId('transaction_user_id')->nullable()->constrained('transaction_users')->nullOnDelete();
            $table->foreignId('accessory_id')->nullable()->constrained('accessories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transaction_user_id');
            $table->dropConstrainedForeignId('accessory_id');
        });
    }
};
