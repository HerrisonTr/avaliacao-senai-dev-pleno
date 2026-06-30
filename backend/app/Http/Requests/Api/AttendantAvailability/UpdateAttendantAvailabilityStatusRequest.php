<?php

namespace App\Http\Requests\Api\AttendantAvailability;

use App\Models\AttendantAvailability;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAttendantAvailabilityStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('attendant-availability.update') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'active.required' => 'Informe se a disponibilidade está ativa.',
            'active.boolean' => 'O campo situação deve ser verdadeiro ou falso.',
        ];
    }

    public function attributes(): array
    {
        return [
            'active' => 'situação',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || ! $this->boolean('active')) {
                return;
            }

            /** @var AttendantAvailability|null $availability */
            $availability = $this->route('availability');

            if (! $availability instanceof AttendantAvailability) {
                return;
            }

            $startTime = $availability->start_time?->format('H:i');
            $endTime = $availability->end_time?->format('H:i');

            if ($startTime === null || $endTime === null) {
                return;
            }

            $hasOverlap = AttendantAvailability::hasActiveOverlap(
                $availability->attendant_id,
                $availability->day_of_week,
                $startTime,
                $endTime,
                $availability->getKey(),
            );

            if ($hasOverlap) {
                $validator->errors()->add(
                    'active',
                    'Não é possível ativar esta disponibilidade porque existe sobreposição com outro horário ativo.'
                );
            }
        });
    }
}
