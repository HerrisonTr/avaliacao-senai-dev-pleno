<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppointmentStatus;
use App\Exceptions\AppointmentConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Appointments\AvailableAttendantsRequest;
use App\Http\Requests\Api\Appointments\AvailableTimesRequest;
use App\Http\Requests\Api\Appointments\ListAppointmentsRequest;
use App\Http\Requests\Api\Appointments\StoreAppointmentRequest;
use App\Http\Requests\Api\Appointments\UpdateAppointmentRequest;
use App\Http\Requests\Api\Appointments\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
use App\Services\AppointmentSchedulingService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AppointmentsController extends Controller
{
    public function __construct(
        private readonly AppointmentSchedulingService $schedulingService,
    ) {}

    /**
     * Lista os agendamentos conforme os filtros informados.
     */
    public function index(ListAppointmentsRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $appointments = Appointment::query()
            ->with(['attendant:id,name', 'service:id,name,price,active'])
            ->whereHas('attendant')
            ->when(
                isset($filters['appointment_date']),
                fn ($query) => $query->whereDate('appointment_date', $filters['appointment_date'])
            )
            ->when(
                isset($filters['attendant_id']),
                fn ($query) => $query->where('attendant_id', $filters['attendant_id'])
            )
            ->when(
                isset($filters['service_id']),
                fn ($query) => $query->where('service_id', $filters['service_id'])
            )
            ->when(
                isset($filters['status']),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'data' => $appointments,
        ], Response::HTTP_OK);
    }

    /**
     * Lista os horários disponíveis para um atendente em uma data.
     */
    public function availableTimes(AvailableTimesRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            return response()->json([
                'data' => $this->schedulingService->availableTimes(
                    $data['appointment_date'],
                    (int) $data['attendant_id'],
                    isset($data['ignore_appointment_id']) ? (int) $data['ignore_appointment_id'] : null,
                ),
            ], Response::HTTP_OK);
        } catch (AppointmentConflictException $exception) {
            return $this->conflictResponse($exception);
        }
    }

    /**
     * Lista os atendentes disponíveis para o período informado.
     */
    public function availableAttendants(AvailableAttendantsRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json([
            'data' => $this->schedulingService->availableAttendants(
                $data['appointment_date'],
                $data['start_time'],
                $data['end_time'],
                isset($data['attendant_id']) ? (int) $data['attendant_id'] : null,
            ),
        ], Response::HTTP_OK);
    }

    /**
     * Exibe os dados de um agendamento.
     */
    public function show(Appointment $appointment): JsonResponse
    {
        if ($response = $this->ensureAppointmentAttendantExists($appointment)) {
            return $response;
        }

        $appointment->load(['attendant:id,name', 'service:id,name,price,active']);

        return response()->json([
            'data' => $appointment,
        ], Response::HTTP_OK);
    }

    /**
     * Cadastra um novo agendamento.
     */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->schedulingService->create($request->validated());

            return response()->json([
                'message' => 'Agendamento cadastrado com sucesso.',
                'data' => $appointment,
            ], Response::HTTP_CREATED);
        } catch (AppointmentConflictException $exception) {
            return $this->conflictResponse($exception);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível cadastrar o agendamento.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualiza os dados de um agendamento.
     */
    public function update(
        UpdateAppointmentRequest $request,
        Appointment $appointment
    ): JsonResponse {
        if ($response = $this->ensureAppointmentAttendantExists($appointment)) {
            return $response;
        }

        try {
            $appointment = $this->schedulingService->update(
                $appointment,
                $request->validated(),
            );

            return response()->json([
                'message' => 'Agendamento atualizado com sucesso.',
                'data' => $appointment,
            ], Response::HTTP_OK);
        } catch (AppointmentConflictException $exception) {
            return $this->conflictResponse($exception);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível atualizar o agendamento.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualiza o status de um agendamento.
     */
    public function updateStatus(
        UpdateAppointmentStatusRequest $request,
        Appointment $appointment
    ): JsonResponse {
        if ($response = $this->ensureAppointmentAttendantExists($appointment)) {
            return $response;
        }

        try {
            $status = AppointmentStatus::from($request->validated()['status']);
            $appointment = $this->schedulingService->changeStatus($appointment, $status);

            return response()->json([
                'message' => 'Status do agendamento atualizado com sucesso.',
                'data' => $appointment,
            ], Response::HTTP_OK);
        } catch (AppointmentConflictException $exception) {
            return $this->conflictResponse($exception);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível atualizar o status do agendamento.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retorna uma resposta para conflitos de agendamento.
     */
    private function conflictResponse(AppointmentConflictException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'alternative_attendants' => $exception->alternativeAttendants,
        ], Response::HTTP_CONFLICT);
    }

    private function ensureAppointmentAttendantExists(Appointment $appointment): ?JsonResponse
    {
        if ($appointment->attendant()->exists()) {
            return null;
        }

        return response()->json([
            'message' => 'Agendamento não encontrado.',
        ], Response::HTTP_NOT_FOUND);
    }
}
