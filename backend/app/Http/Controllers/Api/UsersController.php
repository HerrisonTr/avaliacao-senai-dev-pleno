<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Users\DeleteUserRequest;
use App\Http\Requests\Api\Users\StoreUserRequest;
use App\Http\Requests\Api\Users\UpdateUserPasswordRequest;
use App\Http\Requests\Api\Users\UpdateUserRequest;
use App\Http\Requests\Api\Users\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UsersController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $users = User::query()
                ->with('roles:id,name')
                ->orderBy('id')
                ->get()
                ->map(fn (User $user) => $this->userPayload($user))
                ->values();

            return response()->json([
                'data' => $users,
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível listar os usuários.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            /** @var Role $role */
            $role = Role::query()->findOrFail($validatedData['role_id']);

            $user = DB::transaction(function () use ($validatedData, $role): User {
                $user = User::query()->create([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => $validatedData['password'],
                ]);

                $user->syncRoles([$role]);
                $user->load('roles:id,name');

                return $user;
            });

            return response()->json([
                'message' => 'Usuário cadastrado com sucesso.',
                'user' => $this->userPayload($user),
            ], Response::HTTP_CREATED);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível cadastrar o usuário.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            /** @var Role $role */
            $role = Role::query()->findOrFail($validatedData['role_id']);

            $updatedUser = DB::transaction(function () use ($validatedData, $role, $user): User {
                $user->update([
                    'name' => $validatedData['name']
                ]);

                $user->syncRoles([$role]);
                $user->load('roles:id,name');

                return $user;
            });

            return response()->json([
                'message' => 'Usuário atualizado com sucesso.',
                'user' => $this->userPayload($updatedUser),
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível atualizar o usuário.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updatePassword(UpdateUserPasswordRequest $request, User $user): JsonResponse
    {
        try {
            $usuarioAutenticado = $request->user();
            $podeAtualizarSenha = $usuarioAutenticado?->can('user.update')
                || ($usuarioAutenticado?->hasRole('Atendente') && $usuarioAutenticado->is($user));

            if (! $podeAtualizarSenha) {
                return response()->json([
                    'message' => 'Você não tem permissão para atualizar esta senha.',
                ], Response::HTTP_FORBIDDEN);
            }

            $user->update([
                'password' => $request->validated()['password'],
            ]);

            return response()->json([
                'message' => 'Senha atualizada com sucesso.',
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível atualizar a senha do usuário.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): JsonResponse
    {
        try {
            $user->update([
                'active' => $request->validated()['active'],
            ]);
            $user->load('roles:id,name');

            return response()->json([
                'message' => 'Status do usuário atualizado com sucesso.',
                'user' => $this->userPayload($user),
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível atualizar o status do usuário.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(DeleteUserRequest $request, User $user): JsonResponse
    {
        try {
            $user->delete();

            return response()->json([
                'message' => 'Usuário removido com sucesso.',
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível remover o usuário.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first()?->name,
            'active' => $user->active,
        ];
    }
}
