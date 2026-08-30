<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id('tour_id');

            $table->foreignId('category_id')
                ->constrained('categories', 'category_id')
                ->cascadeOnDelete();

            $table->foreignId('destination_id')
                ->constrained('destinations', 'destination_id')
                ->cascadeOnDelete();

            $table->string('title');
            $table->decimal('base_price', 10, 2);
            $table->unsignedInteger('duration_days');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};