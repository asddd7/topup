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
        Schema::table('midtrans_transactions', function (Blueprint $table) {
            $table
                ->string('midtrans_payment_type', 100)
                ->nullable()
                ->after('gross_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('midtrans_transactions', function (Blueprint $table) {
            //
        });
    }
};
