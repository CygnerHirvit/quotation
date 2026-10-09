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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('section', 20);
            $table->string('name');
            $table->unsignedInteger('rate')->default(0);
            $table->unsignedSmallInteger('qty')->default(1);
            $table->string('note')->nullable();
            $table->string('delivery')->nullable();
            $table->boolean('included')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['quotation_id', 'section', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
