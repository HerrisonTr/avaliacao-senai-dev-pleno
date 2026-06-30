<?php

namespace App\Http\Requests\Api\Appointments;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class AppointmentRequest extends FormRequest
{
    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'attendant_id' => ['required', 'integer', 'exists:users,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'attendant_id.required' => 'Informe o atendente.',
            'attendant_id.integer' => 'O atendente informado é inválido.',
            'attendant_id.exists' => 'O atendente informado não foi encontrado.',
            'service_id.required' => 'Informe o serviço.',
            'service_id.integer' => 'O serviço informado é inválido.',
            'service_id.exists' => 'O serviço informado não foi encontrado.',
            'appointment_date.required' => 'Informe a data do agendamento.',
            'appointment_date.date_format' => 'A data do agendamento deve estar no formato AAAA-MM-DD.',
            'start_time.required' => 'Informe o horário de início.',
            'start_time.date_format' => 'O horário de início deve estar no formato HH:MM.',
            'end_time.required' => 'Informe o horário de fim.',
            'end_time.date_format' => 'O horário de fim deve estar no formato HH:MM.',
            'end_time.after' => 'O horário de fim deve ser maior que o horário de início.',
            'customer_name.required' => 'Informe o nome do cliente.',
            'customer_name.string' => 'O nome do cliente deve ser um texto.',
            'customer_name.max' => 'O nome do cliente deve ter no máximo 255 caracteres.',
            'customer_phone.required' => 'Informe o telefone do cliente.',
            'customer_phone.string' => 'O telefone do cliente deve ser um texto.',
            'customer_phone.max' => 'O telefone do cliente deve ter no máximo 30 caracteres.',
            'customer_email.email' => 'Informe um e-mail válido para o cliente.',
            'customer_email.max' => 'O e-mail do cliente deve ter no máximo 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'attendant_id' => 'atendente',
            'service_id' => 'serviço',
            'appointment_date' => 'data do agendamento',
            'start_time' => 'horário de início',
            'end_time' => 'horário de fim',
            'customer_name' => 'nome do cliente',
            'customer_phone' => 'telefone do cliente',
            'customer_email' => 'e-mail do cliente',
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

            if (! $this->isSlotBoundary($startTime, $slotIntervalMinutes)) {
                $validator->errors()->add(
                    'start_time',
                    "O horário de início deve respeitar intervalos de {$slotIntervalMinutes} minutos."
                );
            }

            if (! $this->isSlotBoundary($endTime, $slotIntervalMinutes)) {
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
                    'O início do agendamento deve estar no futuro.'
                );
            }
        });
    }

    private function isSlotBoundary(string $time, int $slotIntervalMinutes): bool
    {
        return ((int) substr($time, 3, 2)) % $slotIntervalMinutes === 0;
    }
}
