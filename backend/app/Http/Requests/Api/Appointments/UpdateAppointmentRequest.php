<?php

namespace App\Http\Requests\Api\Appointments;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends AppointmentRequest
{
    /**
     * Regras específicas para atualizar um agendamento.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'attendant_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn (Builder $query): Builder => $query->where('active', true)
                ),
            ],
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where(
                    fn (Builder $query): Builder => $query->where('active', true)
                ),
            ],
            'appointment_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'attendant_id.exists' => 'O atendente informado não foi encontrado ou está inativo.',
            'service_id.exists' => 'O serviço informado não foi encontrado ou está inativo.',
            'appointment_date.after_or_equal' => 'A data do agendamento não pode estar no passado.',
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('appointment.update') ?? false;
    }
}
