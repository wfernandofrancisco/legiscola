<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\BudgetServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBudgetRequest;
use App\Http\Requests\Api\UpdateBudgetRequest;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(private BudgetServiceInterface $budgetService) {}

    /**
     * Lista orçamentos com filtros e paginação.
     * GET /api/v1/budgets
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'tenant_id', 'user_id', 'search']);
        $perPage = (int) $request->get('per_page', 15);

        $budgets = $this->budgetService->listBudgets($filters, $perPage);

        activity('budgets')
            ->causedBy($request->user())
            ->log('Listagem de orçamentos visualizada');

        return response()->json($budgets);
    }

    /**
     * Cria um novo orçamento.
     * POST /api/v1/budgets
     */
    public function store(StoreBudgetRequest $request): JsonResponse
    {
        $data = $request->only([
            'titulo',
            'descricao',
            'tenant_id',
            'subtotal',
            'desconto',
            'total',
            'status',
            'validade',
            'observacoes'
        ]);

        $budget = $this->budgetService->createBudget(
            $data,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Orçamento criado com sucesso.',
            'budget'  => $budget->load(['tenant', 'user']),
        ], 201);
    }

    /**
     * Exibe um orçamento específico.
     * GET /api/v1/budgets/{budget}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $budget = Budget::withTrashed()->with(['tenant', 'user'])->findOrFail($id);

        activity('budgets')
            ->causedBy($request->user())
            ->performedOn($budget)
            ->log('Orçamento visualizado');

        return response()->json($budget);
    }

    /**
     * Atualiza um orçamento.
     * PUT /api/v1/budgets/{budget}
     */
    public function update(UpdateBudgetRequest $request, int $id): JsonResponse
    {
        $data = $request->only([
            'titulo',
            'descricao',
            'tenant_id',
            'subtotal',
            'desconto',
            'total',
            'status',
            'validade',
            'observacoes'
        ]);

        $budget = $this->budgetService->updateBudget($id, $data);

        return response()->json([
            'message' => 'Orçamento atualizado com sucesso.',
            'budget'  => $budget,
        ]);
    }

    /**
     * Remove (soft delete) um orçamento.
     * DELETE /api/v1/budgets/{budget}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->budgetService->deleteBudget($id);

        return response()->json(['message' => 'Orçamento removido com sucesso.']);
    }

    /**
     * Aprova um orçamento.
     * POST /api/v1/budgets/{budget}/approve
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $this->budgetService->approveBudget($id);

        activity('budgets')
            ->causedBy($request->user())
            ->log("Orçamento #{$id} aprovado");

        return response()->json(['message' => 'Orçamento aprovado com sucesso.']);
    }

    /**
     * Rejeita um orçamento.
     * POST /api/v1/budgets/{budget}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $this->budgetService->rejectBudget($id);

        activity('budgets')
            ->causedBy($request->user())
            ->log("Orçamento #{$id} rejeitado");

        return response()->json(['message' => 'Orçamento rejeitado com sucesso.']);
    }
}
