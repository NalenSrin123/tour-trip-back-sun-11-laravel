<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_itineraries', function (Blueprint $table) {
            $table->id('itinerary_id');

            $table->foreignId('tour_id')
                ->constrained('tours', 'tour_id')
                ->cascadeOnDelete();

            $table->unsignedInteger('day_number');
            $table->string('activity_title');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_itineraries');
    }
};