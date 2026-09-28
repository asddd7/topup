<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite stores enum columns as text and does not support MySQL's
        // ALTER TABLE ... CHANGE syntax. The original enum declaration is
        // still valid for SQLite test databases without this statement.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Mengubah kolom enum dengan menambahkan 'Cancelled'
        DB::statement("ALTER TABLE `payment_logs` CHANGE `status` `status` ENUM('Pending', 'Paid', 'Failed', 'Expired', 'Refund', 'Waiting Payment', 'Cancelled') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Mengembalikan kolom enum ke nilai semula (tanpa 'Cancelled')
        DB::statement("ALTER TABLE `payment_logs` CHANGE `status` `status` ENUM('Pending', 'Paid', 'Failed', 'Expired', 'Refund', 'Waiting Payment') NOT NULL");
    }
};
