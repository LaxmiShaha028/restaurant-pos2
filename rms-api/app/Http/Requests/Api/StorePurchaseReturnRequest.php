<?php

namespace App\Http\Requests\Api;

use App\Models\PurchaseReturnItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StorePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('inventory.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->exists('notes')) {
            $notes = trim((string) ($this->input('notes') ?? ''));
            $data['notes'] = $notes !== '' ? $notes : null;
        }

        $items = $this->input('items');

        if (is_array($items)) {
            $data['items'] = collect($items)
                ->map(static function (mixed $item): mixed {
                    if (!is_array($item)) {
                        return $item;
                    }

                    if (array_key_exists('reason', $item)) {
                        $item['reason'] = strtolower(
                            trim((string) ($item['reason'] ?? ''))
                        );
                    }

                    if (array_key_exists('notes', $item)) {
                        $notes = trim((string) ($item['notes'] ?? ''));
                        $item['notes'] = $notes !== '' ? $notes : null;
                    }

                    return $item;
                })
                ->values()
                ->all();
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

    public function rules(): array
    {
        return [
            'returned_at' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*.warehouse_stock_lot_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('warehouse_stock_lots', 'id'),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'decimal:0,4',
                'gt:0',
                'max:9999999999.9999',
            ],

            'items.*.reason' => [
                'required',
                'string',
                Rule::in(PurchaseReturnItem::allowedReasons()),
            ],

            'items.*.notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' =>
                'At least one warehouse lot is required for Purchase Return.',

            'items.*.warehouse_stock_lot_id.distinct' =>
                'The same warehouse lot cannot be returned twice in one Purchase Return.',

            'items.*.warehouse_stock_lot_id.exists' =>
                'The selected warehouse lot was not found.',

            'items.*.quantity.gt' =>
                'Return quantity must be greater than zero.',

            'items.*.reason.in' =>
                'The selected Purchase Return reason is invalid.',
        ];
    }
}
