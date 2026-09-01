<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->string('moogold_order_id')
                ->nullable()
                ->after('status');

            $table->string('moogold_status')
                ->nullable()
                ->after('moogold_order_id');

            $table->json('moogold_response')
                ->nullable()
                ->after('moogold_status');

            $table->timestamp('moogold_ordered_at')
                ->nullable()
                ->after('moogold_response');

        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropColumn([
                'moogold_order_id',
                'moogold_status',
                'moogold_response',
                'moogold_ordered_at',
            ]);

        });
    }
};  