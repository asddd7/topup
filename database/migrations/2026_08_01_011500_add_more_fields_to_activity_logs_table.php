<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {

            $table->string('action')->nullable()->after('module');

            $table->unsignedBigInteger('target_id')->nullable()->after('action');

            $table->string('target_name')->nullable()->after('target_id');

            $table->json('old_data')->nullable()->after('target_name');

            $table->json('new_data')->nullable()->after('old_data');

        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {

            $table->dropColumn([
                'action',
                'target_id',
                'target_name',
                'old_data',
                'new_data'
            ]);

        });
    }
};