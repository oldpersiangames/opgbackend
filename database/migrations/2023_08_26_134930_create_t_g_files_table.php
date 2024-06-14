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
        Schema::create('t_g_files', function (Blueprint $table) {
            // $table->id();
            $table->double('chat_id')->nullable();
            $table->integer('message_id')->nullable();
            $table->timestamp('message_date')->nullable();
            $table->string('file_id');
            $table->string('file_unique_id')->primary();
            $table->string('file_name');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->string('date');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_g_files');
    }
};
