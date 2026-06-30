<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'attendant_id',
        'service_id',
        'appointment_date',
        'start_time',
        'end_time',
        'customer_name',
        'customer_phone',
        'customer_email',
        'service_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'attendant_id' => 'integer',
            'service_id' => 'integer',
            'appointment_date' => 'date:Y-m-d',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'service_price' => 'decimal:2',
            'status' => AppointmentStatus::class,
        ];
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attendant_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::Scheduled->value);
    }

    public function scopeOverlappingScheduled(
        Builder $query,
        int $attendantId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreId = null
    ): Builder {
        return $query
            ->where('attendant_id', $attendantId)
            ->whereDate('appointment_date', $date)
            ->scheduled()
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId));
    }

    public static function hasScheduledOverlap(
        int $attendantId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreId = null
    ): bool {
        return static::query()
            ->overlappingScheduled($attendantId, $date, $startTime, $endTime, $ignoreId)
            ->exists();
    }
}
