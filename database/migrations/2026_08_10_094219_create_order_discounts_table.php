<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_discounts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('discount_id')
                ->constrained('discounts')
                ->restrictOnDelete();

            $table->decimal('discount_amount', 15, 2)
                ->default(0);

            $table->timestamps();

            $table->unique([
                'order_id',
                'discount_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_discounts');
    }
};