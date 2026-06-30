<?php

namespace App\Http\Requests\Api\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class AvailableTimesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointment.list') ?? false;
    }

    public function rules(): array
    {
        return [
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'attendant_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_date.required' => 'Informe a data do agendamento.',
            'appointment_date.date_format' => 'A data deve estar no formato AAAA-MM-DD.',
            'appointment_date.after_or_equal' => 'A data não pode estar no passado.',
            'attendant_id.required' => 'Informe o atendente.',
            'attendant_id.integer' => 'O atendente informado é inválido.',
            'attendant_id.exists' => 'O atendente informado não foi encontrado.',
        ];
    }
}
