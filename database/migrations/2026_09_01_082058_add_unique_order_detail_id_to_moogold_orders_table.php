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
        Schema::table(
            'moogold_orders',
            function (Blueprint $table) {

                $table->unique(
                    'order_detail_id',
                    'moogold_orders_order_detail_id_unique'
                );

            }
        );
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'moogold_orders',
            function (Blueprint $table) {

                $table->dropUnique(
                    'moogold_orders_order_detail_id_unique'
                );

            }
        );
    }
};