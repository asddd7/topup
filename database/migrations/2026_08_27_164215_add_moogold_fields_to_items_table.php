<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {

            $table->unsignedBigInteger(
                'moogold_product_id'
            )
                ->nullable()
                ->after('id');


            $table->string(
                'moogold_offer_id'
            )
                ->nullable()
                ->after('moogold_product_id');


            $table->unsignedTinyInteger(
                'moogold_type'
            )
                ->nullable()
                ->after('moogold_offer_id');

        });
    }


    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {

            $table->dropColumn([
                'moogold_product_id',
                'moogold_offer_id',
                'moogold_type',
            ]);

        });
    }
};