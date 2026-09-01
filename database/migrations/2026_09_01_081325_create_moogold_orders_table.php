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
        Schema::create(
            'moogold_orders',
            function (Blueprint $table) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | RELATION
                |--------------------------------------------------------------------------
                */

                $table->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();


                $table->foreignId('order_detail_id')
                    ->constrained('order_details')
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | LOCAL ITEM SNAPSHOT
                |--------------------------------------------------------------------------
                */

                $table->foreignId('item_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | EXTERNAL ORDER
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'external_order_id'
                )
                ->unique();


                /*
                |--------------------------------------------------------------------------
                | MOOGOLD PRODUCT
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'moogold_category_id'
                )
                ->nullable()
                ->index();


                $table->string(
                    'moogold_product_id'
                )
                ->nullable()
                ->index();


                $table->string(
                    'moogold_variation_id'
                )
                ->nullable()
                ->index();


                /*
                |--------------------------------------------------------------------------
                | MOOGOLD ORDER RESULT
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'moogold_order_id'
                )
                ->nullable()
                ->index();


                $table->string(
                    'moogold_status'
                )
                ->nullable()
                ->index();


                /*
                |--------------------------------------------------------------------------
                | REQUEST & RESPONSE
                |--------------------------------------------------------------------------
                */

                $table->json(
                    'request_payload'
                )
                ->nullable();


                $table->json(
                    'response_payload'
                )
                ->nullable();


                /*
                |--------------------------------------------------------------------------
                | ERROR
                |--------------------------------------------------------------------------
                */

                $table->text(
                    'error_message'
                )
                ->nullable();


                /*
                |--------------------------------------------------------------------------
                | RETRY
                |--------------------------------------------------------------------------
                */

                $table->unsignedInteger(
                    'attempts'
                )
                ->default(0);


                $table->timestamp(
                    'last_attempt_at'
                )
                ->nullable();


                /*
                |--------------------------------------------------------------------------
                | PROCESSING TIME
                |--------------------------------------------------------------------------
                */

                $table->timestamp(
                    'ordered_at'
                )
                ->nullable();


                $table->timestamp(
                    'completed_at'
                )
                ->nullable();


                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | INDEX
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'order_id',
                    'moogold_status'
                ]);

            }
        );
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'moogold_orders'
        );
    }
};