<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {

            $table->enum('trigger_type', [
                'voucher',          // kode voucher
                'automatic',        // otomatis
                'new_user',         // user baru
                'first_order',      // order pertama
                'birthday',         // ulang tahun
                'flash_sale',       // flash sale
                'event',            // event tertentu
                'payment_method'    // metode pembayaran
            ])
            ->default('voucher')
            ->after('is_active');

            $table->decimal('minimum_purchase',12,2)
                ->default(0)
                ->after('trigger_type');

            $table->integer('usage_limit')
                ->nullable()
                ->after('minimum_purchase');

            $table->integer('usage_per_user')
                ->default(1)
                ->after('usage_limit');

            $table->integer('quota_used')
                ->default(0)
                ->after('usage_per_user');

        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {

            $table->dropColumn([
                'trigger_type',
                'minimum_purchase',
                'usage_limit',
                'usage_per_user',
                'quota_used',
            ]);

        });
    }
};