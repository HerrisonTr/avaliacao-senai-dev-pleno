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
        Schema::create('attendant_availabilities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendant_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 0 = Domingo, 1 = Segunda, ..., 6 = Sabado
            $table->unsignedTinyInteger('day_of_week');

            $table->time('start_time');
            $table->time('end_time');

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index([
                'attendant_id',
                'day_of_week',
                'active',
            ]);

            $table->unique([
                'attendant_id',
                'day_of_week',
                'start_time',
                'end_time',
            ], 'attendant_availability_unique_window');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendant_availabilities');
    }
};
