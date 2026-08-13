<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUserRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserServiceInterface $userService) {}

    /**
     * Lista usuários com filtros e paginação.
     * GET /api/v1/users
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['user_type', 'status', 'search']);
        $perPage = (int) $request->get('per_page', 15);

        $users = $this->userService->listUsers($filters, $perPage);

        // Registro de log de visualização
        activity('users')
            ->causedBy($request->user())
            ->log('Listagem de usuários visualizada');

        return response()->json($users);
    }

    /**
     * Cria um novo usuário.
     * POST /api/v1/users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->only([
            'name',
            'email',
            'password',
            'password_confirmation',
            'phone',
            'cpf',
            'user_type',
            'status',
            'avatar'
        ]);
        $data['user_type'] = $data['user_type'] ?? User::TYPE_CLIENTE;
        $data['status']    = $data['status'] ?? User::STATUS_PENDENTE;

        $user = $this->userService->createUser($data);

        return response()->json([
            'message' => 'Usuário criado com sucesso.',
            'user'    => $user->load('roles'),
        ], 201);
    }

    /**
     * Exibe um usuário específico.
     * GET /api/v1/users/{user}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        // Registro de log de visualização
        activity('users')
            ->causedBy($request->user())
            ->performedOn($user)
            ->log('Usuário visualizado');

        return response()->json($user->load(['roles', 'companies']));
    }

    /**
     * Atualiza um usuário.
     * PUT /api/v1/users/{user}
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $data = $request->only([
            'name',
            'email',
            'password',
            'password_confirmation',
            'phone',
            'cpf',
            'user_type',
            'status',
            'avatar'
        ]);

        $user = $this->userService->updateUser($id, $data);

        return response()->json([
            'message' => 'Usuário atualizado com sucesso.',
            'user'    => $user->load('roles'),
        ]);
    }

    /**
     * Remove (soft delete) um usuário.
     * DELETE /api/v1/users/{user}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return response()->json(['message' => 'Usuário removido com sucesso.']);
    }

    /**
     * Ativa um usuário.
     * POST /api/v1/users/{user}/activate
     */
    public function activate(int $id): JsonResponse
    {
        $this->userService->activateUser($id);

        return response()->json(['message' => 'Usuário ativado com sucesso.']);
    }

    /**
     * Desativa um usuário.
     * POST /api/v1/users/{user}/deactivate
     */
    public function deactivate(int $id): JsonResponse
    {
        $this->userService->deactivateUser($id);

        return response()->json(['message' => 'Usuário desativado com sucesso.']);
    }

    /**
     * Altera o tipo do usuário e sincroniza role.
     * PATCH /api/v1/users/{user}/type
     */
    public function changeType(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'user_type' => ['required', 'in:funcionario,dono_empresa,cliente'],
        ]);

        $this->userService->changeUserType($id, $request->user_type);

        return response()->json(['message' => 'Tipo de usuário alterado com sucesso.']);
    }
}
