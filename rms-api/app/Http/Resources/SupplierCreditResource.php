<?php

namespace App\Http\Resources;

use App\Models\PurchaseReturnSettlement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class SupplierCreditResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        $applications =
            $this->relationLoaded(
                'creditApplications'
            )
                ? $this->creditApplications
                : collect();


        $issuedAmount =
            round(
                (float) $this->amount,
                2
            );


        $appliedAmount =
            round(
                (float) $applications
                    ->sum(
                        'amount'
                    ),
                2
            );


        $availableAmount =
            round(
                max(
                    0,
                    $issuedAmount
                    -
                    $appliedAmount
                ),
                2
            );


        $purchaseReturn =
            $this->relationLoaded(
                'purchaseReturn'
            )
                ? $this->purchaseReturn
                : null;


        return [
            'id' =>
                (int) $this->id,

            'settlement_no' =>
                $this->settlement_no,

            'supplier_id' =>
                (int) $this->supplier_id,

            'supplier_name' =>
                $purchaseReturn?->supplier_name
                ??
                $this->supplier?->supplier_name
                ??
                'Unknown Supplier',

            'purchase_return_id' =>
                $purchaseReturn
                    ? (int) $purchaseReturn->id
                    : null,

            'return_no' =>
                $purchaseReturn?->return_no,

            'issued_at' =>
                $this->settled_at
                    ?->toISOString(),

            'issued_at_label' =>
                $this->settled_at
                    ?->format(
                        'd M Y, h:i A'
                    ),

            'issued_amount' =>
                $issuedAmount,

            'issued_amount_formatted' =>
                $this->money(
                    $issuedAmount
                ),

            'applied_amount' =>
                $appliedAmount,

            'applied_amount_formatted' =>
                $this->money(
                    $appliedAmount
                ),

            'available_amount' =>
                $availableAmount,

            'available_amount_formatted' =>
                $this->money(
                    $availableAmount
                ),

            'status' =>
                $availableAmount <= 0
                    ? 'used'
                    : (
                        $appliedAmount > 0
                            ? 'partially_used'
                            : 'available'
                    ),

            'status_label' =>
                $availableAmount <= 0
                    ? 'Fully Used'
                    : (
                        $appliedAmount > 0
                            ? 'Partially Used'
                            : 'Available'
                    ),

            'applications' =>
                SupplierCreditApplicationResource::collection(
                    $this->whenLoaded(
                        'creditApplications'
                    )
                ),

            'notes' =>
                $this->notes,
        ];
    }


    private function money(
        mixed $value
    ): string {

        return '৳ '
            .
            number_format(
                (float) $value,
                2
            );
    }
}
