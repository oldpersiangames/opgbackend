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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('ia_id')->nullable();

            // $table->json('photos')->nullable();
            $table->boolean('selling')->default(false);
            $table->string('title_fa')->nullable();
            $table->string('title_en')->nullable();

            $table->json('release_dates')->nullable();
            $table->json('prices')->nullable();

            $table->json('released_on_en')->nullable();
            $table->json('released_on_fa')->nullable();

            $table->json('defects_fa')->nullable();
            $table->json('defects_en')->nullable();

            $table->text('notes_fa')->nullable();
            $table->text('notes_en')->nullable();

            $table->text('content_fa')->nullable();
            $table->text('content_en')->nullable();

            $table->json('tgfiles')->nullable();
            $table->json('files')->nullable();

            $table->json('links')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
