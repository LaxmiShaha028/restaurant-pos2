<?php

namespace App\Http\Resources;

use App\Models\PurchaseReturnItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PurchaseReturnItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'purchase_return_id' => (int) $this->purchase_return_id,
            'warehouse_stock_lot_id' => (int) $this->warehouse_stock_lot_id,
            'raw_material_id' => (int) $this->raw_material_id,

            'purchase_order_id' =>
                $this->purchase_order_id !== null
                    ? (int) $this->purchase_order_id
                    : null,

            'purchase_order' =>
                $this->whenLoaded(
                    'purchaseOrder',
                    function (): ?array {
                        if (!$this->purchaseOrder) {
                            return null;
                        }

                        $po = $this->purchaseOrder;

                        return [
                            'id' => (int) $po->id,

                            'total_amount' =>
                                round(
                                    (float) $po->total_amount,
                                    2
                                ),

                            'paid_amount' =>
                                round(
                                    (float) $po->paid_amount,
                                    2
                                ),

                            'return_credit_applied' =>
                                round(
                                    (float) (
                                        $po->return_credit_applied
                                        ??
                                        0
                                    ),
                                    2
                                ),

                            'due_amount' =>
                                round(
                                    max(
                                        0,
                                        (float) $po->due_amount
                                    ),
                                    2
                                ),

                            'due_amount_formatted' =>
                                $this->money(
                                    $po->due_amount
                                ),
                        ];
                    },
                    null
                ),

            'purchase_order_receipt_id' =>
                $this->purchase_order_receipt_id !== null
                    ? (int) $this->purchase_order_receipt_id
                    : null,

            'item_name' => $this->item_name,
            'unit' => $this->unit,
            'lot_no' => $this->lot_no,
            'supplier_batch_no' => $this->supplier_batch_no,

            'manufactured_at' =>
                $this->manufactured_at?->format('Y-m-d'),

            'expiry_date' =>
                $this->expiry_date?->format('Y-m-d'),

            'expiry_date_label' =>
                $this->expiry_date?->format('d M Y'),

            'quantity' => $this->decimal($this->quantity),
            'quantity_formatted' =>
                $this->quantityLabel($this->quantity, $this->unit),

            'lot_quantity_before' =>
                $this->decimal($this->lot_quantity_before),

            'lot_quantity_after' =>
                $this->decimal($this->lot_quantity_after),

            'warehouse_quantity_before' =>
                $this->decimal($this->warehouse_quantity_before),

            'warehouse_quantity_after' =>
                $this->decimal($this->warehouse_quantity_after),

            'return_unit_cost' =>
                $this->decimal($this->return_unit_cost),

            'return_unit_cost_formatted' =>
                $this->money($this->return_unit_cost),

            'expected_supplier_credit' =>
                $this->decimal($this->expected_supplier_credit),

            'expected_supplier_credit_formatted' =>
                $this->money($this->expected_supplier_credit),

            'inventory_average_unit_cost' =>
                $this->decimal($this->inventory_average_unit_cost),

            'inventory_average_unit_cost_formatted' =>
                $this->money($this->inventory_average_unit_cost),

            'inventory_value_reduction' =>
                $this->decimal($this->inventory_value_reduction),

            'inventory_value_reduction_formatted' =>
                $this->money($this->inventory_value_reduction),

            'valuation_variance' =>
                $this->decimal($this->valuation_variance),

            'valuation_variance_formatted' =>
                $this->signedMoney($this->valuation_variance),

            'reason' => $this->reason,
            'reason_label' =>
                PurchaseReturnItem::reasonLabel($this->reason),

            'reason_notes' => $this->reason_notes,

            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function decimal(mixed $value): float
    {
        return round((float) $value, 4);
    }

    private function quantityLabel(mixed $value, ?string $unit): string
    {
        $number = number_format((float) $value, 4, '.', '');
        $number = rtrim(rtrim($number, '0'), '.');

        return trim("{$number} {$unit}");
    }

    private function money(mixed $value): string
    {
        return '৳ ' . number_format((float) $value, 2);
    }

    private function signedMoney(mixed $value): string
    {
        $amount = round((float) $value, 4);
        $prefix = $amount > 0 ? '+' : '';

        return $prefix . '৳ ' . number_format($amount, 2);
    }
}
