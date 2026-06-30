<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendantAvailability extends Model
{
    protected $table = 'attendant_availabilities';

    protected $fillable = [
        'attendant_id',
        'day_of_week',
        'start_time',
        'end_time',
        'active',
    ];

    protected $casts = [
        'attendant_id' => 'integer',
        'day_of_week' => 'integer',
        'active' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attendant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeForAttendant(Builder $query, int $attendantId): Builder
    {
        return $query->where('attendant_id', $attendantId);
    }

    public function scopeForDayOfWeek(Builder $query, int $dayOfWeek): Builder
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    public function scopeOverlappingActive(
        Builder $query,
        int $attendantId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $ignoreId = null
    ): Builder {
        return $query
            ->forAttendant($attendantId)
            ->forDayOfWeek($dayOfWeek)
            ->active()
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId));
    }

    public static function hasActiveOverlap(
        int $attendantId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $ignoreId = null
    ): bool {
        return static::query()
            ->overlappingActive($attendantId, $dayOfWeek, $startTime, $endTime, $ignoreId)
            ->exists();
    }

    public static function findActive(): Collection
    {
        return static::query()
            ->with([
                'attendant:id,name',
            ])
            ->active()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public static function findByAttendant(int $attendantId): Collection
    {
        return static::query()
            ->with([
                'attendant:id,name',
            ])
            ->forAttendant($attendantId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public static function findByDayOfWeek(int $dayOfWeek): Collection
    {
        return static::query()
            ->forDayOfWeek($dayOfWeek)
            ->active()
            ->with([
                'attendant:id,name',
            ])
            ->orderBy('start_time')
            ->get();
    }
}
