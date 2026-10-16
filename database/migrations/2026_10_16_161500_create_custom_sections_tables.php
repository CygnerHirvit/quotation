<?php

use App\Models\Studio;
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
        Schema::create('custom_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->string('position', 30)->default('after_coverage');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['studio_id', 'sort_order']);
        });

        Schema::create('custom_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_section_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['custom_section_id', 'sort_order']);
        });

        // Seed a default "Scope of Work" section with starter items for each existing studio
        $starterItems = [
            [
                'title' => 'Creative Planning',
                'description' => 'Every wedding begins with understanding your vision. We create a personalized coverage plan, mood board, shot list, timeline, and creative direction in collaboration with you and your planner.',
            ],
            [
                'title' => 'The Couple',
                'description' => 'From intimate portraits to unscripted moments, we document your journey with a blend of editorial elegance and genuine emotion.',
            ],
            [
                'title' => 'The Celebrations',
                'description' => 'Every ritual, ceremony, and celebration is covered with a documentary approach, preserving every tradition and every meaningful detail.',
            ],
            [
                'title' => 'Family & Friends',
                'description' => 'The laughter, happy tears, heartfelt hugs, speeches, and candid interactions that make your celebration truly unforgettable.',
            ],
            [
                'title' => 'Love Stories & Interviews',
                'description' => 'Thoughtfully recorded conversations, voice messages, and candid interviews with the couple, parents, siblings, cousins, and close friends—woven into your wedding film to make it deeply personal.',
            ],
            [
                'title' => 'Trends & Social Content',
                'description' => 'Behind-the-scenes moments, vertical reels, cinematic transitions, trending edits, and social-first content created throughout the celebrations.',
            ],
        ];

        foreach (Studio::all() as $studio) {
            $sectionId = DB::table('custom_sections')->insertGetId([
                'studio_id' => $studio->id,
                'title' => 'Scope of Work',
                'tagline' => 'Thoughtfully planned. Beautifully captured. Timelessly remembered.',
                'position' => 'after_coverage',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($starterItems as $index => $item) {
                DB::table('custom_section_items')->insert([
                    'custom_section_id' => $sectionId,
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_section_items');
        Schema::dropIfExists('custom_sections');
    }
};

