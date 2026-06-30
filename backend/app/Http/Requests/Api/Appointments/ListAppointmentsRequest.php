<?php

namespace App\Http\Requests\Api\Appointments;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAppointmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointment.list') ?? false;
    }

    public function rules(): array
    {
        return [
            'appointment_date' => ['nullable', 'date_format:Y-m-d'],
            'attendant_id' => ['nullable', 'integer', 'exists:users,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_date.date_format' => 'A data deve estar no formato AAAA-MM-DD.',
            'attendant_id.integer' => 'O atendente informado é inválido.',
            'attendant_id.exists' => 'O atendente informado não foi encontrado.',
            'service_id.integer' => 'O serviço informado é inválido.',
            'service_id.exists' => 'O serviço informado não foi encontrado.',
            'status.enum' => 'O status informado é inválido.',
        ];
    }
}
