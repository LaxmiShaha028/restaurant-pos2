<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                (int) $this->id,

            'journal_number' =>
                $this->journal_number,

            'journal_date' =>
                $this->journal_date
                    ?->format('Y-m-d'),

            'journal_date_label' =>
                $this->journal_date
                    ?->format('d M Y'),

            'entry_type' =>
                $this->entry_type,

            'entry_type_label' =>
                match (
                    $this->entry_type
                ) {

                    'manual' =>
                        'Manual',

                    'source' =>
                        'System Source',

                    'reversal' =>
                        'Reversal',

                    default =>
                        ucfirst(
                            (string) $this->entry_type
                        ),
                },

            'status' =>
                $this->status,

            'status_label' =>
                $this->status === 'reversed'
                    ? 'Reversed'
                    : 'Posted',

            'description' =>
                $this->description,

            'source_type' =>
                $this->source_type,

            'source_id' =>
                $this->source_id !== null
                    ? (int) $this->source_id
                    : null,

            'source_revision' =>
                $this->source_revision !== null
                    ? (int) $this->source_revision
                    : null,

            'reversal_of_id' =>
                $this->reversal_of_id !== null
                    ? (int) $this->reversal_of_id
                    : null,

            'total_debit' =>
                (float) $this->total_debit,

            'total_credit' =>
                (float) $this->total_credit,

            'total_debit_formatted' =>
                $this->money(
                    $this->total_debit
                ),

            'total_credit_formatted' =>
                $this->money(
                    $this->total_credit
                ),

            'posted_at' =>
                $this->posted_at
                    ?->toISOString(),

            'posted_at_label' =>
                $this->posted_at
                    ?->format(
                        'd M Y, h:i A'
                    ),

            'posted_by_name' =>
                $this->poster?->name
                ??
                'System',

            'reversed_at' =>
                $this->reversed_at
                    ?->toISOString(),

            'reversed_by_name' =>
                $this->reverser?->name,

            'reversal_reason' =>
                $this->reversal_reason,

            'can_reverse' =>
                $this->canReverse(),

            'lines' =>
                JournalLineResource::collection(
                    $this->whenLoaded(
                        'lines'
                    )
                ),

            'reversal_entry' =>
                $this->whenLoaded(
                    'reversalEntry',
                    function (): ?array {

                        if (! $this->reversalEntry) {
                            return null;
                        }


                        return [

                            'id' =>
                                (int) $this
                                    ->reversalEntry
                                    ->id,

                            'journal_number' =>
                                $this
                                    ->reversalEntry
                                    ->journal_number,

                            'journal_date' =>
                                $this
                                    ->reversalEntry
                                    ->journal_date
                                    ?->format('Y-m-d'),
                        ];
                    }
                ),

            'original_entry' =>
                $this->whenLoaded(
                    'originalEntry',
                    function (): ?array {

                        if (! $this->originalEntry) {
                            return null;
                        }


                        return [

                            'id' =>
                                (int) $this
                                    ->originalEntry
                                    ->id,

                            'journal_number' =>
                                $this
                                    ->originalEntry
                                    ->journal_number,
                        ];
                    }
                ),
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
