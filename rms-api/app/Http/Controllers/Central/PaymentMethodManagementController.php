<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Payment;
use App\Models\Central\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentMethodManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List Payment Methods
    |--------------------------------------------------------------------------
    */

    public function index(): JsonResponse
    {
        $methods = PaymentMethod::on('central')
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        $data = $methods
            ->map(
                function (PaymentMethod $method): array {
                    return $this->serializeMethod($method);
                }
            )
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Payment methods retrieved successfully.',
            'data' => $data,
            'errors' => null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Create Payment Method
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
            ],

            'status' => [
                'required',
                'boolean',
            ],
        ]);

        $code = Str::lower(
            trim(
                $validated['code']
            )
        );

        if (
            PaymentMethod::on('central')
                ->where(
                    'code',
                    $code
                )
                ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'A payment method with this code already exists.',
                'data' => null,
                'errors' => [
                    'code' => [
                        'The payment method code has already been taken.',
                    ],
                ],
            ], 422);
        }

        $method = PaymentMethod::on('central')
            ->create([
                'name' =>
                    trim($validated['name']),
                'code' =>
                    $code,
                'status' =>
                    (bool) $validated['status'],
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully.',
            'data' => $this->serializeMethod(
                $method->fresh()
            ),
            'errors' => null,
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Payment Method
    |--------------------------------------------------------------------------
    |
    | The method code is intentionally immutable after creation because
    | historical Payment.payment_method values may already reference it.
    |
    */

    public function update(
        Request $request,
        int $method
    ): JsonResponse {
        $methodModel = PaymentMethod::on('central')
            ->find($method);

        if (! $methodModel) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'status' => [
                'required',
                'boolean',
            ],
        ]);

        $methodModel->forceFill([
            'name' =>
                trim($validated['name']),
            'status' =>
                (bool) $validated['status'],
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully.',
            'data' => $this->serializeMethod(
                $methodModel->fresh()
            ),
            'errors' => null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Payment Method
    |--------------------------------------------------------------------------
    |
    | Payment methods are never hard-deleted here because historical payments
    | retain the method code.
    |
    */

    public function updateStatus(
        Request $request,
        int $method
    ): JsonResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'boolean',
            ],
        ]);

        $methodModel = PaymentMethod::on('central')
            ->find($method);

        if (! $methodModel) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $methodModel->forceFill([
            'status' =>
                (bool) $validated['status'],
        ])->save();

        return response()->json([
            'success' => true,
            'message' =>
                $methodModel->status
                    ? 'Payment method enabled successfully.'
                    : 'Payment method disabled successfully.',
            'data' => $this->serializeMethod(
                $methodModel->fresh()
            ),
            'errors' => null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Serializer
    |--------------------------------------------------------------------------
    */

    private function serializeMethod(
        PaymentMethod $method
    ): array {
        $totalPayments =
            Payment::on('central')
                ->where(
                    'payment_method',
                    $method->code
                )
                ->count();

        $paidPayments =
            Payment::on('central')
                ->where(
                    'payment_method',
                    $method->code
                )
                ->where(
                    'status',
                    'paid'
                )
                ->count();

        return [
            'id' => $method->id,
            'name' => $method->name,
            'code' => $method->code,
            'status' => (bool) $method->status,
            'created_at' => $method->created_at,
            'updated_at' => $method->updated_at,

            'usage' => [
                'total_payments' =>
                    $totalPayments,
                'paid_payments' =>
                    $paidPayments,
            ],
        ];
    }
}
