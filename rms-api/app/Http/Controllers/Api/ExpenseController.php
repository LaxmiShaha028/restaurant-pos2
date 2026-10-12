<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExpenseIndexRequest;
use App\Http\Requests\Api\StoreExpenseRequest;
use App\Http\Requests\Api\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasAuditLog;

class ExpenseController extends Controller
{
    use HasAuditLog;
    public function __construct(
        private readonly ExpenseService $expenseService
    ) {
    }



    /*
    |--------------------------------------------------------------------------
    | Expense List
    |--------------------------------------------------------------------------
    */

    public function index(
        ExpenseIndexRequest $request
    ): JsonResponse {

        $expenses =
            $this->expenseService
                ->getExpenses(
                    $request->validated()
                );


        return response()->json([

            'success' =>
                true,


            'message' =>
                'Expenses loaded successfully.',


            'data' =>
                ExpenseResource::collection(
                    $expenses
                ),


            'meta' => [

                'current_page' =>
                    $expenses->currentPage(),

                'last_page' =>
                    $expenses->lastPage(),

                'per_page' =>
                    $expenses->perPage(),

                'total' =>
                    $expenses->total(),

                'from' =>
                    $expenses->firstItem(),

                'to' =>
                    $expenses->lastItem(),

            ],

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Expense Summary
    |--------------------------------------------------------------------------
    */

    public function summary(
        ExpenseIndexRequest $request
    ): JsonResponse {


        return response()->json([

            'success' =>
                true,


            'message' =>
                'Expense summary loaded successfully.',


            'data' =>
                $this->expenseService
                    ->getSummary(
                        $request->validated()
                    ),

        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | Expense Options
    |--------------------------------------------------------------------------
    |
    | Dropdown data:
    | Category
    | Payment methods
    |
    */

    public function options(): JsonResponse
    {

        return response()->json([

            'success' =>
                true,


            'message' =>
                'Expense options loaded successfully.',


            'data' => [
                'categories' => ExpenseCategory::query()
                    ->active()
                    ->select([
                        'id',
                        'name',
                    ])
                    ->orderBy('name')
                    ->get(),
                'payment_methods' =>
                    Expense::paymentMethodOptions(),
            ],

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Store Expense
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreExpenseRequest $request
    ): JsonResponse {


        $expense =
            $this->expenseService
                ->createExpense(

                    $request->validated(),

                    Auth::user()

                );
        $this->audit(
    'expenses',
    'created',
    $expense,
    null,
    $expense->toArray(),
    'Created expense: '.$expense->id
);


        $expense->load([
            'category',
            'creator',
        ]);



        return response()->json([

            'success' =>
                true,


            'message' =>
                'Expense created successfully.',


            'data' =>
                new ExpenseResource(
                    $expense
                ),

        ], 201);

    }



    /*
    |--------------------------------------------------------------------------
    | Show Expense
    |--------------------------------------------------------------------------
    */

    public function show(
        Expense $expense
    ): JsonResponse {


        $expense->load([
            'category',
            'creator',
            'updater',
        ]);



        return response()->json([

            'success' =>
                true,


            'message' =>
                'Expense loaded successfully.',


            'data' =>
                new ExpenseResource(
                    $expense
                ),

        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | Update Expense
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateExpenseRequest $request,
        Expense $expense
    ): JsonResponse {

        $oldData = $expense->toArray();
        $expense =
            $this->expenseService
                ->updateExpense(

                    $expense,

                    $request->validated(),

                    Auth::user()

                );
        $expense->refresh();

$this->audit(
    'expenses',
    'updated',
    $expense,
    $oldData,
    $expense->toArray(),
    'Updated expense: '.$expense->id
);


        $expense->load([
            'category',
            'creator',
            'updater',
        ]);




        return response()->json([

            'success' =>
                true,


            'message' =>
                'Expense updated successfully.',


            'data' =>
                new ExpenseResource(
                    $expense
                ),

        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | Delete Expense
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Expense $expense
    ): JsonResponse {

        $oldData = $expense->toArray();
        $this->expenseService
            ->deleteExpense(
                $expense
            );
        $this->audit(
    'expenses',
    'deleted',
    $expense,
    $oldData,
    null,
    'Deleted expense: '.$expense->id
);

        return response()->json([

            'success' =>
                true,


            'message' =>
                'Expense deleted successfully.',


            'data' =>
                null,

        ]);

    }

}
