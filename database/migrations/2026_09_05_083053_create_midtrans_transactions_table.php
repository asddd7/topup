<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'midtrans_transactions',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId('order_id')
                    ->unique()
                    ->constrained()
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | MIDTRANS IDENTIFIER
                |--------------------------------------------------------------------------
                */

                $table->string('midtrans_order_id')
                    ->unique();

                $table->string('snap_token')
                    ->nullable()
                    ->unique();

                $table->string('snap_redirect_url')
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | TRANSACTION DATA
                |--------------------------------------------------------------------------
                */

                $table->string('transaction_id')
                    ->nullable()
                    ->index();

                $table->string('transaction_status')
                    ->nullable()
                    ->index();

                $table->string('payment_type')
                    ->nullable();

                $table->string('fraud_status')
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | AMOUNT
                |--------------------------------------------------------------------------
                */

                $table->decimal(
                    'gross_amount',
                    15,
                    2
                );


                /*
                |--------------------------------------------------------------------------
                | RESPONSE
                |--------------------------------------------------------------------------
                */

                $table->json('request_payload')
                    ->nullable();

                $table->json('response_payload')
                    ->nullable();

                $table->json('notification_payload')
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | TIME
                |--------------------------------------------------------------------------
                */

                $table->timestamp('paid_at')
                    ->nullable();

                $table->timestamp('expired_at')
                    ->nullable();

                $table->timestamps();
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'midtrans_transactions'
        );
    }
};