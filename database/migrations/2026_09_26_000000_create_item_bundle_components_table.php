<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_bundle_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_item_id')
                ->constrained('items')
                ->cascadeOnDelete();
            $table->foreignId('component_item_id')
                ->constrained('items')
                ->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique([
                'bundle_item_id',
                'component_item_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_bundle_components');
    }
};