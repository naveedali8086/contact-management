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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            // Email, Mobile, Other (twitter, instagram,...) and Mobile may/may not be on WhatsApp
            $table->string('channel');
            $table->string('channel_other')->nullable();
            $table->string('channel_value')->unique();
            $table->unsignedBigInteger('contactable_id');
            $table->string('contactable_type');
            $table->string('belongs_to');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
