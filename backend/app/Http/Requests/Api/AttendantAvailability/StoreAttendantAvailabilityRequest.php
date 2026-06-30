<?php

namespace App\Http\Requests\Api\AttendantAvailability;

use App\Models\AttendantAvailability;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAttendantAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('attendant-availability.create') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attendant_id' => ['required', 'integer', 'exists:users,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'attendant_id.required' => 'Informe o atendente.',
            'attendant_id.integer' => 'O atendente informado é inválido.',
            'attendant_id.exists' => 'O atendente informado não foi encontrado.',
            'day_of_week.required' => 'Informe o dia da semana.',
            'day_of_week.integer' => 'O dia da semana informado é inválido.',
            'day_of_week.between' => 'O dia da semana deve estar entre 0 e 6.',
            'start_time.required' => 'Informe o horário de início.',
            'start_time.date_format' => 'O horário de início deve estar no formato HH:MM.',
            'end_time.required' => 'Informe o horário de fim.',
            'end_time.date_format' => 'O horário de fim deve estar no formato HH:MM.',
            'end_time.after' => 'O horário de fim deve ser maior que o horário de início.',
            'active.required' => 'Informe se a disponibilidade está ativa.',
            'active.boolean' => 'O campo situação deve ser verdadeiro ou falso.',
        ];
    }

    public function attributes(): array
    {
        return [
            'attendant_id' => 'atendente',
            'day_of_week' => 'dia da semana',
            'start_time' => 'horário de início',
            'end_time' => 'horário de fim',
            'active' => 'situação',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || ! $this->boolean('active')) {
                return;
            }

            $hasOverlap = AttendantAvailability::hasActiveOverlap(
                $this->integer('attendant_id'),
                $this->integer('day_of_week'),
                $this->string('start_time')->toString(),
                $this->string('end_time')->toString(),
            );

            if ($hasOverlap) {
                $validator->errors()->add(
                    'start_time',
                    'Já existe uma disponibilidade ativa sobreposta para este atendente neste dia.'
                );
            }
        });
    }
}
