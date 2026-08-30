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
        Schema::create('tour_schedule', function (Blueprint $table) {
            $table->id('schedule_id');

            $table->foreignId('tour_id')
                ->constrained('tours', 'tour_id')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('guide_id');

            $table->date('start_date');
            $table->date('end_date');

            $table->unsignedInteger('max_capacity');
            $table->unsignedInteger('booked_seats')->default(0);

            $table->enum('status', ['UPCOMING', 'COMPLETED'])
                ->default('UPCOMING');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_schedule');
    }
};
