<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Added as a plain column first because the tour_schedule table
            // is created later in the migration list.
            $table->unsignedBigInteger('schedule_id');

            $table->string('booking_code')->unique();
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
