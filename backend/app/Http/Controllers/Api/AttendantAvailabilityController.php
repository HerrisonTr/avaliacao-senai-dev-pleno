<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AttendantAvailability\StoreAttendantAvailabilityRequest;
use App\Http\Requests\Api\AttendantAvailability\UpdateAttendantAvailabilityRequest;
use App\Http\Requests\Api\AttendantAvailability\UpdateAttendantAvailabilityStatusRequest;
use App\Models\AttendantAvailability;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AttendantAvailabilityController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'attendant_id' => ['nullable', 'integer', 'exists:users,id'],
                'day_of_week' => ['nullable', 'integer', 'between:0,6'],
                'active' => ['nullable', 'boolean'],
            ]);

            $attendantId = $filters['attendant_id'] ?? null;
            $dayOfWeek = $filters['day_of_week'] ?? null;
            $active = $filters['active'] ?? null;

            $query = AttendantAvailability::query()
                ->with('attendant:id,name');

            if ($attendantId !== null) {
                $query->where('attendant_id', $attendantId);
            }

            if ($dayOfWeek !== null) {
                $query->where('day_of_week', $dayOfWeek);
            }

            if (array_key_exists('active', $filters)) {
                $query->where('active', $active);
            }

            $availabilities = $query
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            return response()->json([
                'data' => $availabilities,
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível listar as disponibilidades.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(AttendantAvailability $availability): JsonResponse
    {
        try {
            $availability->load('attendant:id,name');

            return response()->json([
                'data' => $availability,
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível carregar a disponibilidade.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreAttendantAvailabilityRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $attendantValidationResponse = $this->validateAttendantUser($validatedData['attendant_id']);

            if ($attendantValidationResponse instanceof JsonResponse) {
                return $attendantValidationResponse;
            }

            $availability = AttendantAvailability::query()->create($validatedData);
            $availability->load('attendant:id,name');

            return response()->json([
                'message' => 'Disponibilidade cadastrada com sucesso.',
                'data' => $availability,
            ], Response::HTTP_CREATED);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível cadastrar a disponibilidade.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(
        UpdateAttendantAvailabilityRequest $request,
        AttendantAvailability $availability
    ): JsonResponse {
        try {
            $validatedData = $request->validated();
            $attendantValidationResponse = $this->validateAttendantUser($validatedData['attendant_id']);

            if ($attendantValidationResponse instanceof JsonResponse) {
                return $attendantValidationResponse;
            }

            $availability->update($validatedData);
            $availability->load('attendant:id,name');

            return response()->json([
                'message' => 'Disponibilidade atualizada com sucesso.',
                'data' => $availability,
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível atualizar a disponibilidade.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateStatus(
        UpdateAttendantAvailabilityStatusRequest $request,
        AttendantAvailability $availability
    ): JsonResponse {
        try {
            $availability->update([
                'active' => $request->validated()['active'],
            ]);
            $availability->load('attendant:id,name');

            return response()->json([
                'message' => 'Status da disponibilidade atualizado com sucesso.',
                'data' => $availability,
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível atualizar o status da disponibilidade.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(AttendantAvailability $availability): JsonResponse
    {
        try {
            $availability->delete();

            return response()->json([
                'message' => 'Disponibilidade removida com sucesso.',
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível remover a disponibilidade.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function validateAttendantUser(int $userId): ?JsonResponse
    {
        $user = User::query()->find($userId);

        if (! $user || ! $user->hasRole('Atendente')) {
            return response()->json([
                'message' => 'O usuário informado não pertence ao grupo Atendente.',
                'errors' => [
                    'attendant_id' => [
                        'O usuário informado não pertence ao grupo Atendente.',
                    ],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return null;
    }
}
