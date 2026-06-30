<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AlterarSenhaUsuarioRequest;
use App\Http\Requests\Api\AlterarStatusUsuarioRequest;
use App\Http\Requests\Api\AtualizarUsuarioRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CadastrarUsuarioRequest;
use App\Http\Requests\Api\ExcluirUsuarioRequest;
use App\Http\Requests\Api\ListarUsuariosRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UsersController extends Controller
{
    public function index(ListarUsuariosRequest $request): JsonResponse
    {
        try {
            $usuariosPaginados = User::query()
                ->with('roles:id,name')
                ->orderBy('id')
                ->paginate(15)
                ->through(fn (User $usuario) => $this->userPayload($usuario));

            return response()->json($usuariosPaginados, Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível listar os usuários.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(CadastrarUsuarioRequest $request): JsonResponse
    {
        try {
            $dadosValidados = $request->validated();

            /** @var Role $papel */
            $papel = Role::query()->findOrFail($dadosValidados['role_id']);

            $usuario = DB::transaction(function () use ($dadosValidados, $papel): User {
                $usuario = User::query()->create([
                    'name' => $dadosValidados['name'],
                    'email' => $dadosValidados['email'],
                    'password' => $dadosValidados['password'],
                ]);

                $usuario->syncRoles([$papel]);
                $usuario->load('roles:id,name');

                return $usuario;
            });

            return response()->json([
                'message' => 'Usuário cadastrado com sucesso.',
                'user' => $this->userPayload($usuario),
            ], Response::HTTP_CREATED);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível cadastrar o usuário.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(AtualizarUsuarioRequest $request, User $user): JsonResponse
    {
        try {
            $dadosValidados = $request->validated();

            /** @var Role $papel */
            $papel = Role::query()->findOrFail($dadosValidados['role_id']);

            $usuarioAtualizado = DB::transaction(function () use ($dadosValidados, $papel, $user): User {
                $user->update([
                    'name' => $dadosValidados['name'],
                    'email' => $dadosValidados['email'],
                ]);

                $user->syncRoles([$papel]);
                $user->load('roles:id,name');

                return $user;
            });

            return response()->json([
                'message' => 'Usuário atualizado com sucesso.',
                'user' => $this->userPayload($usuarioAtualizado),
            ], Response::HTTP_OK);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Não foi possível atualizar o usuário.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updatePassword(AlterarSenhaUsuarioRequest $request, User $user): JsonResponse
    {
        try {
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

    public function updateStatus(AlterarStatusUsuarioRequest $request, User $user): JsonResponse
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

    public function destroy(ExcluirUsuarioRequest $request, User $user): JsonResponse
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

    private function userPayload(User $usuario): array
    {
        return [
            'id' => $usuario->id,
            'name' => $usuario->name,
            'email' => $usuario->email,
            'role' => $usuario->roles->first()?->name,
            'active' => $usuario->active,
        ];
    }
}
