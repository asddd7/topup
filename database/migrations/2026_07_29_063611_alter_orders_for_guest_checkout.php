<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            // user boleh kosong
            $table->foreignId('user_id')
                ->nullable()
                ->change();

            // data guest
            $table->string('guest_name')
                ->nullable()
                ->after('user_id');

            $table->string('guest_email')
                ->nullable()
                ->after('guest_name');

            $table->string('guest_phone')
                ->nullable()
                ->after('guest_email');

            $table->uuid('guest_token')
                ->nullable()
                ->unique()
                ->after('guest_phone');

        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropColumn([
                'guest_name',
                'guest_email',
                'guest_phone',
                'guest_token'
            ]);

        });
    }
};