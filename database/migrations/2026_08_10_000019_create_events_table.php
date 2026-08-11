<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('event_categories');
            $table->string('title');
            $table->text('short_description');
            $table->longText('description');
            $table->enum('status', ['encours', 'terminer', 'planifier']);
            $table->boolean('is_featured');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('address');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('list_details')->nullable();
            $table->boolean('is_active');
            $table->string('thumbnail');
            $table->string('video_url_link');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
