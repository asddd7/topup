<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'midtrans_transactions',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | DROP FOREIGN KEY
                |--------------------------------------------------------------------------
                */

                $table->dropForeign([
                    'order_id',
                ]);


                /*
                |--------------------------------------------------------------------------
                | DROP UNIQUE ORDER ID
                |--------------------------------------------------------------------------
                */

                $table->dropUnique(
                    'midtrans_transactions_order_id_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | NORMAL INDEX
                |--------------------------------------------------------------------------
                */

                $table->index(
                    'order_id',
                    'midtrans_transactions_order_id_index'
                );


                /*
                |--------------------------------------------------------------------------
                | UNIQUE PAYMENT ATTEMPT
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'order_id',
                        'attempt_number',
                    ],
                    'midtrans_transactions_order_attempt_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | RESTORE FOREIGN KEY
                |--------------------------------------------------------------------------
                */

                $table->foreign(
                    'order_id',
                    'midtrans_transactions_order_id_foreign'
                )
                    ->references('id')
                    ->on('orders')
                    ->cascadeOnDelete();
            }
        );
    }


    public function down(): void
    {
        Schema::table(
            'midtrans_transactions',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | DROP FOREIGN KEY
                |--------------------------------------------------------------------------
                */

                $table->dropForeign(
                    'midtrans_transactions_order_id_foreign'
                );


                /*
                |--------------------------------------------------------------------------
                | DROP NEW INDEXES
                |--------------------------------------------------------------------------
                */

                $table->dropUnique(
                    'midtrans_transactions_order_attempt_unique'
                );

                $table->dropIndex(
                    'midtrans_transactions_order_id_index'
                );


                /*
                |--------------------------------------------------------------------------
                | RESTORE UNIQUE ORDER ID
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    'order_id',
                    'midtrans_transactions_order_id_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | RESTORE FOREIGN KEY
                |--------------------------------------------------------------------------
                */

                $table->foreign(
                    'order_id',
                    'midtrans_transactions_order_id_foreign'
                )
                    ->references('id')
                    ->on('orders')
                    ->cascadeOnDelete();
            }
        );
    }
};