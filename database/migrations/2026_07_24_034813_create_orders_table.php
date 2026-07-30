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
        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            $table->string('invoice_number')->unique();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('game_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('payment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('discount_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('player_uid');

            $table->string('server_id')->nullable();

            $table->string('nickname')->nullable();

            $table->decimal('subtotal',12,2);

            $table->decimal('discount',12,2)->default(0);

            $table->decimal('total_price',12,2);

            $table->string('payment_proof')->nullable();

            $table->enum('status',[
                'Pending',
                'Waiting Payment',
                'Paid',
                'Processing',
                'Completed',
                'Cancelled'
            ])->default('Pending');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
