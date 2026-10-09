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
        Schema::create('studios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->string('tagline')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('signature_name')->nullable();
            $table->string('theme', 30)->default('teal_marigold');
            $table->string('accent_color', 9)->nullable();
            $table->string('quote_prefix', 10);
            $table->decimal('gst_percent', 5, 2)->default(18);
            $table->unsignedSmallInteger('validity_days')->default(15);
            $table->boolean('show_breakdown_default')->default(false);
            $table->text('whatsapp_message')->nullable();
            $table->string('cover_wedding_path')->nullable();
            $table->string('cover_corporate_path')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('studios');
    }
};
