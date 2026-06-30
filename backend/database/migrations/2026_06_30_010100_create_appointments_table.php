<?php

use App\Enums\AppointmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendant_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->string('customer_email')->nullable();
            $table->decimal('service_price', 11, 2);
            $table->enum('status', array_column(AppointmentStatus::cases(), 'value'))
                ->default(AppointmentStatus::Scheduled->value);
            $table->timestamps();

            $table->index(
                ['attendant_id', 'appointment_date', 'status'],
                'appointments_attendant_date_status_index'
            );
            $table->index(['service_id', 'appointment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
