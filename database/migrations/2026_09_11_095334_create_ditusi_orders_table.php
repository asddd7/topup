<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'ditusi_orders',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'order_id'
                )->constrained(
                    'orders'
                )->cascadeOnDelete();

                $table->foreignId(
                    'order_detail_id'
                )->constrained(
                    'order_details'
                )->cascadeOnDelete();

                $table->foreignId(
                    'item_id'
                )->constrained(
                    'items'
                )->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | LOCAL IDEMPOTENCY
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'transaction_reference_id'
                )->unique();

                /*
                |--------------------------------------------------------------------------
                | DITUSI
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'ditusi_transaction_id'
                )->nullable()->unique();

                $table->string(
                    'ditusi_product_code'
                );

                $table->string(
                    'ditusi_status'
                )->default(
                    'pending order'
                );

                /*
                |--------------------------------------------------------------------------
                | PAYLOAD
                |--------------------------------------------------------------------------
                */

                $table->json(
                    'request_payload'
                )->nullable();

                $table->json(
                    'response_payload'
                )->nullable();

                $table->text(
                    'error_message'
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | TRACKING
                |--------------------------------------------------------------------------
                */

                $table->unsignedInteger(
                    'attempts'
                )->default(0);

                $table->timestamp(
                    'ordered_at'
                )->nullable();

                $table->timestamp(
                    'completed_at'
                )->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | 1 ORDER DETAIL = 1 DITUSI ORDER
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    'order_detail_id'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ditusi_orders'
        );
    }
};