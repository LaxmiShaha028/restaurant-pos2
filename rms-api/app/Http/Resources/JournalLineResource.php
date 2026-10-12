<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalLineResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                (int) $this->id,

            'ledger_account_id' =>
                (int) $this->ledger_account_id,

            'account_code' =>
                $this->account_code_snapshot,

            'account_name' =>
                $this->account_name_snapshot,

            'account_type' =>
                $this->account_type_snapshot,

            'normal_balance' =>
                $this->normal_balance_snapshot,

            'debit' =>
                (float) $this->debit,

            'credit' =>
                (float) $this->credit,

            'debit_formatted' =>
                $this->money(
                    $this->debit
                ),

            'credit_formatted' =>
                $this->money(
                    $this->credit
                ),

            'memo' =>
                $this->memo,
        ];
    }


    private function money(
        mixed $amount
    ): string {

        return '৳ '
            .
            number_format(
                (float) $amount,
                2
            );
    }
}
