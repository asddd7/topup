<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('midtrans_transactions', function (Blueprint $table) {
        /*
        | Index order_id sudah ada di Railway, jadi TIDAK ditambahkan lagi.
        | Yang perlu ditambahkan hanya unique gabungan dan FK baru.
        */
        $table->unique(
            ['order_id', 'attempt_number'],
            'midtrans_transactions_order_attempt_unique'
        );
        $table->foreign('order_id', 'midtrans_transactions_order_id_foreign')
            ->references('id')->on('orders')
            ->cascadeOnDelete();
    });
}

public function down(): void
{
    Schema::table('midtrans_transactions', function (Blueprint $table) {
        $table->dropForeign('midtrans_transactions_order_id_foreign');
        $table->dropUnique('midtrans_transactions_order_attempt_unique');
        // index order_id TIDAK di-drop di sini, karena bukan migration ini yang membuatnya
    });
}
};