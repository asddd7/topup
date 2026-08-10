<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_usages', function (Blueprint $table) {

            $table->id();

            $table->foreignId('discount_id')
                ->constrained('discounts')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            $table->decimal('discount_amount', 15, 2)
                ->default(0);

            $table->timestamps();

            $table->index([
                'discount_id',
                'user_id'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_usages');
    }
};