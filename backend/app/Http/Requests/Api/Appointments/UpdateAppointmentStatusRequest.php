<?php

namespace App\Http\Requests\Api\Appointments;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointment.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    AppointmentStatus::Completed->value,
                    AppointmentStatus::Cancelled->value,
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Informe o novo status do agendamento.',
            'status.in' => 'O status deve ser completed ou cancelled.',
        ];
    }
}
