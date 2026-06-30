<?php

namespace App\Http\Requests\Api\Appointments;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AvailableAttendantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointment.list') ?? false;
    }

    public function rules(): array
    {
        return [
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'attendant_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_date.required' => 'Informe a data do agendamento.',
            'appointment_date.date_format' => 'A data deve estar no formato AAAA-MM-DD.',
            'start_time.required' => 'Informe o horário de início.',
            'start_time.date_format' => 'O horário de início deve estar no formato HH:MM.',
            'end_time.required' => 'Informe o horário de fim.',
            'end_time.date_format' => 'O horário de fim deve estar no formato HH:MM.',
            'end_time.after' => 'O horário de fim deve ser maior que o horário de início.',
            'attendant_id.integer' => 'O atendente informado é inválido.',
            'attendant_id.exists' => 'O atendente informado não foi encontrado.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->hasAny([
                'appointment_date',
                'start_time',
                'end_time',
            ])) {
                return;
            }

            $startTime = $this->string('start_time')->toString();
            $endTime = $this->string('end_time')->toString();
            $slotIntervalMinutes = (int) config('appointments.slot_interval_minutes');

            if ($this->minute($startTime) % $slotIntervalMinutes !== 0) {
                $validator->errors()->add(
                    'start_time',
                    "O horário de início deve respeitar intervalos de {$slotIntervalMinutes} minutos."
                );
            }

            if ($this->minute($endTime) % $slotIntervalMinutes !== 0) {
                $validator->errors()->add(
                    'end_time',
                    "O horário de fim deve respeitar intervalos de {$slotIntervalMinutes} minutos."
                );
            }

            $startsAt = CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                $this->string('appointment_date')->toString().' '.$startTime,
            )->setSecond(0);

            if (! $startsAt->isFuture()) {
                $validator->errors()->add(
                    'start_time',
                    'O início do período deve estar no futuro.'
                );
            }
        });
    }

    private function minute(string $time): int
    {
        return (int) substr($time, 3, 2);
    }
}
