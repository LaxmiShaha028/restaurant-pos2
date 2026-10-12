<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreExpenseCategoryRequest;
use App\Services\ExpenseCategoryService;
use App\Traits\HasAuditLog;
use Illuminate\Http\JsonResponse;

class ExpenseCategoryController extends Controller
{
    use HasAuditLog;

    public function __construct(
        private readonly ExpenseCategoryService $expenseCategoryService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Store / Reactivate Expense Category
    |--------------------------------------------------------------------------
    |
    | If a category with the same name already exists but is inactive or
    | soft-deleted, it is restored/reactivated instead of creating a duplicate.
    |
    */

    public function store(
        StoreExpenseCategoryRequest $request
    ): JsonResponse {

        $result =
            $this->expenseCategoryService
                ->createOrReactivate(
                    $request->validated()
                );

        $category =
            $result['category'];

        $created =
            $result['created'];

        $oldData =
            $result['old_data'];

        $this->audit(
            'expense_categories',
            $created
                ? 'created'
                : 'updated',
            $category,
            $oldData,
            $category->toArray(),
            $created
                ? 'Created expense category: ' . $category->name
                : 'Reactivated expense category: ' . $category->name
        );

        return response()->json([

            'success' =>
                true,

            'message' =>
                $created
                    ? 'Expense category created successfully.'
                    : 'Existing expense category reactivated successfully.',

            'data' => [
                'id' =>
                    (int) $category->id,

                'name' =>
                    $category->name,

                'description' =>
                    $category->description,

                'is_active' =>
                    (bool) $category->is_active,
            ],

        ], $created ? 201 : 200);
    }
}
