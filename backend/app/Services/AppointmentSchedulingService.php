<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Exceptions\AppointmentConflictException;
use App\Models\Appointment;
use App\Models\AttendantAvailability;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AppointmentSchedulingService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Appointment
    {
        return DB::transaction(function () use ($data): Appointment {
            $attendant = User::query()->lockForUpdate()->find($data['attendant_id']);
            $service = Service::query()->active()->find($data['service_id']);

            $this->ensureAttendantIsValid($attendant);
            $this->ensureServiceIsValid($service);
            $this->ensurePeriodIsAvailable(
                $attendant,
                $data['appointment_date'],
                $data['start_time'],
                $data['end_time'],
            );

            $appointment = Appointment::query()->create([
                ...$data,
                'service_price' => $service->price,
                'status' => AppointmentStatus::Scheduled,
            ]);

            return $appointment->load(['attendant:id,name', 'service:id,name,price,active']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data): Appointment {
            $lockedAppointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->getKey());

            if ($lockedAppointment->status !== AppointmentStatus::Scheduled) {
                throw new AppointmentConflictException(
                    'Apenas agendamentos com status agendado podem ser editados.'
                );
            }

            $attendantIds = array_values(array_unique([
                $lockedAppointment->attendant_id,
                (int) $data['attendant_id'],
            ]));
            sort($attendantIds);

            /** @var Collection<int, User> $attendants */
            $attendants = User::query()
                ->whereKey($attendantIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $attendant = $attendants->firstWhere('id', (int) $data['attendant_id']);
            $service = Service::query()->active()->find($data['service_id']);

            $this->ensureAttendantIsValid($attendant);
            $this->ensureServiceIsValid($service);
            $this->ensurePeriodIsAvailable(
                $attendant,
                $data['appointment_date'],
                $data['start_time'],
                $data['end_time'],
                $lockedAppointment->getKey(),
            );

            $lockedAppointment->update([
                ...$data,
                'service_price' => $service->price,
            ]);

            return $lockedAppointment->load(['attendant:id,name', 'service:id,name,price,active']);
        });
    }

    public function changeStatus(Appointment $appointment, AppointmentStatus $status): Appointment
    {
        return DB::transaction(function () use ($appointment, $status): Appointment {
            $lockedAppointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->getKey());

            if ($lockedAppointment->status !== AppointmentStatus::Scheduled) {
                throw new AppointmentConflictException(
                    'Agendamentos concluídos ou cancelados não podem ter o status alterado.'
                );
            }

            if ($status === AppointmentStatus::Scheduled) {
                throw new AppointmentConflictException('Informe o status concluído ou cancelado.');
            }

            $lockedAppointment->update(['status' => $status]);

            return $lockedAppointment->load(['attendant:id,name', 'service:id,name,price,active']);
        });
    }

    /**
     * @return array{
     *     available: list<array{start_time: string, end_time: string}>,
     *     occupied: list<array{start_time: string, end_time: string}>
     * }
     */
    public function availableTimes(string $date, int $attendantId): array
    {
        $attendant = User::query()->find($attendantId);
        $this->ensureAttendantIsValid($attendant);

        $dayOfWeek = CarbonImmutable::parse($date)->dayOfWeek;
        $availabilities = AttendantAvailability::query()
            ->forAttendant($attendantId)
            ->forDayOfWeek($dayOfWeek)
            ->active()
            ->orderBy('start_time')
            ->get();

        $appointments = Appointment::query()
            ->where('attendant_id', $attendantId)
            ->whereDate('appointment_date', $date)
            ->scheduled()
            ->get(['start_time', 'end_time']);

        $now = CarbonImmutable::now();
        $slotIntervalMinutes = (int) config('appointments.slot_interval_minutes');
        $availableSlots = [];

        // Retorna os agendamentos ocupados no formato consolidado inicio/fim.
        $occupiedAppointments = $appointments
            ->map(fn (Appointment $appointment): array => [
                'start_time' => $appointment->start_time->format('H:i'),
                'end_time' => $appointment->end_time->format('H:i'),
            ])
            ->unique(fn (array $appointment): string => $appointment['start_time'].'-'.$appointment['end_time'])
            ->values()
            ->all();

        foreach ($availabilities as $availability) {
            $cursor = CarbonImmutable::parse($date.' '.$availability->start_time->format('H:i'));
            $availabilityEnd = CarbonImmutable::parse($date.' '.$availability->end_time->format('H:i'));

            // Quebra cada janela de disponibilidade em slots do intervalo configurado.
            while ($cursor->addMinutes($slotIntervalMinutes)->lessThanOrEqualTo($availabilityEnd)) {
                $slotEnd = $cursor->addMinutes($slotIntervalMinutes);
                $startTime = $cursor->format('H:i');
                $endTime = $slotEnd->format('H:i');

                // Verifica se existe qualquer agendamento sobrepondo o slot atual.
                $isOccupied = $appointments->contains(
                    fn (Appointment $appointment): bool => $appointment->start_time->format('H:i') < $endTime
                        && $appointment->end_time->format('H:i') > $startTime
                );

                // Só expõe slots livres e posteriores ao horário atual.
                if (! $isOccupied && $cursor->greaterThan($now)) {
                    $availableSlots[] = [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    ];
                }

                $cursor = $slotEnd;
            }
        }

        return [
            'available' => $availableSlots,
            'occupied' => $occupiedAppointments,
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    public function availableAttendants(
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeAttendantId = null
    ): array {
        $dayOfWeek = CarbonImmutable::parse($date)->dayOfWeek;

        return User::query()
            ->select(['id', 'name'])
            ->where('active', true)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Atendente'))
            ->when(
                $excludeAttendantId !== null,
                fn ($query) => $query->whereKeyNot($excludeAttendantId)
            )
            ->whereHas('attendantAvailabilities', function ($query) use ($dayOfWeek, $startTime, $endTime) {
                $query
                    ->active()
                    ->forDayOfWeek($dayOfWeek)
                    ->where('start_time', '<=', $startTime)
                    ->where('end_time', '>=', $endTime);
            })
            ->whereDoesntHave('appointments', function ($query) use ($date, $startTime, $endTime) {
                $query
                    ->whereDate('appointment_date', $date)
                    ->scheduled()
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
            ])
            ->values()
            ->all();
    }

    private function ensurePeriodIsAvailable(
        User $attendant,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreAppointmentId = null
    ): void {
        $dayOfWeek = CarbonImmutable::parse($date)->dayOfWeek;

        $hasAvailability = AttendantAvailability::query()
            ->forAttendant($attendant->getKey())
            ->forDayOfWeek($dayOfWeek)
            ->active()
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>=', $endTime)
            ->exists();

        if (! $hasAvailability) {
            throw new AppointmentConflictException(
                'O atendente selecionado não possui disponibilidade neste período.',
                $this->availableAttendants($date, $startTime, $endTime, $attendant->getKey()),
            );
        }

        if (Appointment::hasScheduledOverlap(
            $attendant->getKey(),
            $date,
            $startTime,
            $endTime,
            $ignoreAppointmentId,
        )) {
            throw new AppointmentConflictException(
                'O horário selecionado já está ocupado para este atendente.',
                $this->availableAttendants($date, $startTime, $endTime, $attendant->getKey()),
            );
        }
    }

    private function ensureAttendantIsValid(?User $attendant): void
    {
        if (! $attendant || ! $attendant->active || ! $attendant->hasRole('Atendente')) {
            throw new AppointmentConflictException(
                'O usuário informado não é um atendente ativo.'
            );
        }
    }

    private function ensureServiceIsValid(?Service $service): void
    {
        if (! $service) {
            throw new AppointmentConflictException(
                'O serviço informado não existe ou está inativo.'
            );
        }
    }
}
