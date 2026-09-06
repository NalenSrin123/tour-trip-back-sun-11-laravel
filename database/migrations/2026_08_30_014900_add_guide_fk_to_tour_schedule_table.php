<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_schedule', function (Blueprint $table) {
            $table->foreign('guide_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tour_schedule', function (Blueprint $table) {
            $table->dropForeign(['guide_id']);
        });
    }
};
