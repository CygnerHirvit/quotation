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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->string('number', 30);
            $table->string('function_type', 30)->default('wedding');
            $table->string('function_side', 20)->nullable();
            $table->string('bride')->nullable();
            $table->string('groom')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_phone', 30)->nullable();
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->boolean('dates_tbd')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->string('gst_mode', 20)->default('included');
            $table->unsignedInteger('manual_total')->nullable();
            $table->boolean('show_breakdown')->default(false);
            $table->text('internal_notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->date('valid_until')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('signed_name')->nullable();
            $table->string('signer_ip', 45)->nullable();
            $table->text('signer_agent')->nullable();
            $table->unsignedInteger('cached_grand_total')->default(0);
            $table->unsignedSmallInteger('cached_crew_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['studio_id', 'number']);
            $table->index(['studio_id', 'status']);
            $table->index(['studio_id', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
