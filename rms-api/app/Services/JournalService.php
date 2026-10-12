<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalService
{
    /*
    |--------------------------------------------------------------------------
    | List
    |--------------------------------------------------------------------------
    */

    public function getEntries(
        array $filters = []
    ): LengthAwarePaginator {

            $query =
                JournalEntry::on('tenant')
                ->with([
                    'poster',
                    'reverser',
                    'reversalEntry',
                    'originalEntry',
                ]);


        if (
            ! empty(
                $filters[
                    'search'
                ]
                ??
                null
            )
        ) {

            $search =
                trim(
                    (string) $filters[
                        'search'
                    ]
                );


            $query->where(
                function (
                    Builder $builder
                ) use ($search): void {

                    $builder
                        ->where(
                            'journal_number',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'source_type',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'lines',
                            function (
                                Builder $lineQuery
                            ) use ($search): void {

                                $lineQuery
                                    ->where(
                                        'account_code_snapshot',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'account_name_snapshot',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'memo',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }


        if (
            ! empty(
                $filters[
                    'status'
                ]
                ??
                null
            )
        ) {
            $query->where(
                'status',
                $filters[
                    'status'
                ]
            );
        }


        if (
            ! empty(
                $filters[
                    'entry_type'
                ]
                ??
                null
            )
        ) {
            $query->where(
                'entry_type',
                $filters[
                    'entry_type'
                ]
            );
        }


        if (
            ! empty(
                $filters[
                    'from_date'
                ]
                ??
                null
            )
        ) {
            $query->whereDate(
                'journal_date',
                '>=',
                $filters[
                    'from_date'
                ]
            );
        }


        if (
            ! empty(
                $filters[
                    'to_date'
                ]
                ??
                null
            )
        ) {
            $query->whereDate(
                'journal_date',
                '<=',
                $filters[
                    'to_date'
                ]
            );
        }


        return $query
            ->orderByDesc(
                'journal_date'
            )
            ->orderByDesc(
                'id'
            )
            ->paginate(
                max(
                    1,
                    min(
                        100,
                        (int) (
                            $filters[
                                'per_page'
                            ]
                            ??
                            20
                        )
                    )
                )
            )
            ->withQueryString();
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function showEntry(
        JournalEntry $entry
    ): JournalEntry {

        return $entry->load([
            'lines',
            'poster',
            'reverser',
            'reversalEntry',
            'originalEntry',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Manual Journal
    |--------------------------------------------------------------------------
    */

    public function postManual(
        array $data,
        User $user
    ): JournalEntry {

        return $this->postEntry(

            journalDate:
                $data[
                    'journal_date'
                ],

            description:
                $data[
                    'description'
                ],

            lines:
                $data[
                    'lines'
                ],

            user:
                $user,

            entryType:
                JournalEntry::TYPE_MANUAL

        );
    }


    /*
    |--------------------------------------------------------------------------
    | Synchronize Business Source
    |--------------------------------------------------------------------------
    |
    | Idempotent rule:
    |
    | Same source + same financial hash
    |     -> do nothing / return current posted journal
    |
    | Same source + changed financial hash
    |     -> reverse old revision
    |     -> post next revision
    |
    | Reversed source + restored source
    |     -> post next revision
    |
    */

    public function syncSource(
        
        string $sourceType,
        int $sourceId,
        string $sourceHash,
        string $journalDate,
        string $description,
        array $lines,
        User $user,
        string $changeReason =
            'Source transaction changed.'
    ): JournalEntry {

        return DB::connection('tenant')->transaction(
            function () use (
                $sourceType,
                $sourceId,
                $sourceHash,
                $journalDate,
                $description,
                $lines,
                $user,
                $changeReason
            ): JournalEntry {

                $latest =
                    JournalEntry::on('tenant')
                        ->where(
                            'source_type',
                            $sourceType
                        )
                        ->where(
                            'source_id',
                            $sourceId
                        )
                        ->orderByDesc(
                            'source_revision'
                        )
                        ->lockForUpdate()
                        ->first();


                /*
                |--------------------------------------------------------------------------
                | Already Correct
                |--------------------------------------------------------------------------
                */

                if (
                    $latest
                    &&
                    $latest->status ===
                    JournalEntry::STATUS_POSTED
                    &&
                    $latest->source_hash !== null
                    &&
                    hash_equals(
                        $latest->source_hash,
                        $sourceHash
                    )
                ) {
                    return $latest->load([
                        'lines',
                        'poster',
                        'reversalEntry',
                        'originalEntry',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Reverse Old Posted Revision
                |--------------------------------------------------------------------------
                */

                if (
                    $latest
                    &&
                    $latest->status ===
                    JournalEntry::STATUS_POSTED
                ) {

                    $this->reverse(

                        entry:
                            $latest,

                        reason:
                            $changeReason,

                        reversalDate:
                            $journalDate,

                        user:
                            $user
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Refresh Latest Source Revision State
                    |--------------------------------------------------------------------------
                    */

                    $latest =
                        JournalEntry::on('tenant')
                            ->where(
                                'source_type',
                                $sourceType
                            )
                            ->where(
                                'source_id',
                                $sourceId
                            )
                            ->orderByDesc(
                                'source_revision'
                            )
                            ->lockForUpdate()
                            ->first();
                }


                $revision =
                    (
                        (int) (
                            $latest
                                ?->source_revision
                            ??
                            0
                        )
                    )
                    +
                    1;


                return $this->postEntry(

                    journalDate:
                        $journalDate,

                    description:
                        $description,

                    lines:
                        $lines,

                    user:
                        $user,

                    entryType:
                        JournalEntry::TYPE_SOURCE,

                    sourceType:
                        $sourceType,

                    sourceId:
                        $sourceId,

                    sourceRevision:
                        $revision,

                    sourceHash:
                        $sourceHash

                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Compatibility Source Post
    |--------------------------------------------------------------------------
    |
    | Kept for code that may already call postSource().
    |
    */

    public function postSource(
        string $sourceType,
        int $sourceId,
        string $journalDate,
        string $description,
        array $lines,
        User $user
    ): JournalEntry {

        $sourceHash =
            hash(
                'sha256',
                json_encode(
                    [
                        'source_type' =>
                            $sourceType,

                        'source_id' =>
                            $sourceId,

                        'journal_date' =>
                            $journalDate,

                        'lines' =>
                            $lines,
                    ],
                    JSON_THROW_ON_ERROR
                )
            );


        return $this->syncSource(

            sourceType:
                $sourceType,

            sourceId:
                $sourceId,

            sourceHash:
                $sourceHash,

            journalDate:
                $journalDate,

            description:
                $description,

            lines:
                $lines,

            user:
                $user,

            changeReason:
                'Source transaction was reposted.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reverse Current Source Revision
    |--------------------------------------------------------------------------
    */

    public function reverseCurrentSource(
        string $sourceType,
        int $sourceId,
        string $reason,
        string $reversalDate,
        User $user
    ): ?JournalEntry {

        return DB::connection('tenant')->transaction(
            function () use (
                $sourceType,
                $sourceId,
                $reason,
                $reversalDate,
                $user
            ): ?JournalEntry {

                $latest =
                    JournalEntry::on('tenant')
                        ->where(
                            'source_type',
                            $sourceType
                        )
                        ->where(
                            'source_id',
                            $sourceId
                        )
                        ->orderByDesc(
                            'source_revision'
                        )
                        ->lockForUpdate()
                        ->first();


                if (
                    ! $latest
                    ||
                    $latest->status !==
                    JournalEntry::STATUS_POSTED
                ) {
                    return null;
                }


                return $this->reverse(

                    entry:
                        $latest,

                    reason:
                        $reason,

                    reversalDate:
                        $reversalDate,

                    user:
                        $user
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reverse
    |--------------------------------------------------------------------------
    */

    public function reverse(
        JournalEntry $entry,
        string $reason,
        string $reversalDate,
        User $user
    ): JournalEntry {

        return DB::connection('tenant')->transaction(
            function () use (
                $entry,
                $reason,
                $reversalDate,
                $user
            ): JournalEntry {

                $locked =
                    JournalEntry::on('tenant')
                        ->with([
                            'lines',
                            'reversalEntry',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $entry->id
                        );


                if (
                    ! $locked->canReverse()
                ) {
                    throw ValidationException::withMessages([
                        'journal' => [
                            'This journal entry cannot be reversed.',
                        ],
                    ]);
                }


                $reversalLines =
                    $locked
                        ->lines
                        ->map(
                            function (
                                JournalLine $line
                            ): array {

                                return [

                                    'ledger_account_id' =>
                                        $line
                                            ->ledger_account_id,

                                    'debit' =>
                                        (float) $line
                                            ->credit,

                                    'credit' =>
                                        (float) $line
                                            ->debit,

                                    'memo' =>
                                        'Reversal of line #'
                                        .
                                        $line->id
                                        .
                                        (
                                            $line->memo
                                                ? ' — '
                                                    .
                                                    $line->memo
                                                : ''
                                        ),
                                ];
                            }
                        )
                        ->all();


                $reversal =
                    $this->postEntry(

                        journalDate:
                            $reversalDate,

                        description:
                            'REVERSAL: '
                            .
                            $locked
                                ->journal_number
                            .
                            ' — '
                            .
                            $reason,

                        lines:
                            $reversalLines,

                        user:
                            $user,

                        entryType:
                            JournalEntry::TYPE_REVERSAL,

                        reversalOfId:
                            $locked->id

                    );


                $locked->update([

                    'status' =>
                        JournalEntry::STATUS_REVERSED,

                    'reversed_at' =>
                        now(),

                    'reversed_by' =>
                        $user->id,

                    'reversal_reason' =>
                        $reason,
                ]);


                return $reversal->fresh([
                    'lines',
                    'poster',
                    'originalEntry',
                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Core Posting Engine
    |--------------------------------------------------------------------------
    */

    private function postEntry(
        string $journalDate,
        string $description,
        array $lines,
        User $user,
        string $entryType,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?int $sourceRevision = null,
        ?string $sourceHash = null,
        ?int $reversalOfId = null
    ): JournalEntry {
//         \Log::info('postEntry reached', [
//     'data' => func_get_args()
// ]);

        return DB::connection('tenant')->transaction(
            function () use (
                $journalDate,
                $description,
                $lines,
                $user,
                $entryType,
                $sourceType,
                $sourceId,
                $sourceRevision,
                $sourceHash,
                $reversalOfId
            ): JournalEntry {

                $prepared =
                    $this->prepareLines(
                        $lines
                    );


                $totalDebit =
                    round(
                        (float) $prepared
                            ->sum(
                                'debit'
                            ),
                        2
                    );


                $totalCredit =
                    round(
                        (float) $prepared
                            ->sum(
                                'credit'
                            ),
                        2
                    );


                if (
                    $totalDebit <= 0
                    ||
                    $totalCredit <= 0
                ) {
                    throw ValidationException::withMessages([
                        'lines' => [
                            'A journal must contain both a debit and a credit amount.',
                        ],
                    ]);
                }


                if (
                    abs(
                        $totalDebit
                        -
                        $totalCredit
                    )
                    >
                    0.009
                ) {
                    throw ValidationException::withMessages([
                        'lines' => [
                            'Journal is not balanced. Total Debit must exactly equal Total Credit.',
                        ],
                    ]);
                }
                $entry =
                    JournalEntry::on('tenant')->create([

                        'journal_number' =>
                            null,

                        'journal_date' =>
                            $journalDate,

                        'entry_type' =>
                            $entryType,

                        'status' =>
                            JournalEntry::STATUS_POSTED,

                        'description' =>
                            trim(
                                $description
                            ),

                        'source_type' =>
                            $sourceType,

                        'source_id' =>
                            $sourceId,

                        'source_revision' =>
                            $sourceRevision,

                        'source_hash' =>
                            $sourceHash,

                        'reversal_of_id' =>
                            $reversalOfId,

                        'total_debit' =>
                            $totalDebit,

                        'total_credit' =>
                            $totalCredit,

                        'posted_at' =>
                            now(),

                        'posted_by' =>
                            $user->id,

                        'reversed_at' =>
                            null,

                        'reversed_by' =>
                            null,

                        'reversal_reason' =>
                            null,
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Journal Number
                |--------------------------------------------------------------------------
                */

                $entry->journal_number =
                    'JV-'
                    .
                    Carbon::parse(
                        $journalDate
                    )->format(
                        'Ym'
                    )
                    .
                    '-'
                    .
                    str_pad(
                        (string) $entry->id,
                        6,
                        '0',
                        STR_PAD_LEFT
                    );


                $entry->save();

                \Log::info('Before JournalLine Create');

                foreach (
                    $prepared
                    as $line
                ) {

                    JournalLine::on('tenant')->create([

                        'journal_entry_id' =>
                            $entry->id,

                        'ledger_account_id' =>
                            $line[
                                'account'
                            ]->id,

                        'account_code_snapshot' =>
                            $line[
                                'account'
                            ]->code,

                        'account_name_snapshot' =>
                            $line[
                                'account'
                            ]->name,

                        'account_type_snapshot' =>
                            $line[
                                'account'
                            ]->account_type,

                        'normal_balance_snapshot' =>
                            $line[
                                'account'
                            ]->normal_balance,

                        'debit' =>
                            $line[
                                'debit'
                            ],

                        'credit' =>
                            $line[
                                'credit'
                            ],

                        'memo' =>
                            $line[
                                'memo'
                            ],
                    ]);
                }

                \Log::info('Before Transaction Commit Return', [
                    'journal_id' => $entry->id,
                    'exists' => JournalEntry::on('tenant')
                        ->where('id', $entry->id)
                        ->exists(),
                ]);
                return $entry->fresh([
                    'lines',
                    'poster',
                    'reversalEntry',
                    'originalEntry',
                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare / Validate Lines
    |--------------------------------------------------------------------------
    */

    private function prepareLines(
        array $lines
    ): Collection {

        if (
            count($lines) < 2
        ) {
            throw ValidationException::withMessages([
                'lines' => [
                    'At least two journal lines are required.',
                ],
            ]);
        }


        return collect(
            $lines
        )
            ->map(
                function (
                    array $line,
                    int $index
                ): array {

                    $debit =
                        round(
                            (float) (
                                $line[
                                    'debit'
                                ]
                                ??
                                0
                            ),
                            2
                        );


                    $credit =
                        round(
                            (float) (
                                $line[
                                    'credit'
                                ]
                                ??
                                0
                            ),
                            2
                        );


                    if (
                        $debit < 0
                        ||
                        $credit < 0
                    ) {
                        throw ValidationException::withMessages([
                            "lines.{$index}" => [
                                'Debit and Credit cannot be negative.',
                            ],
                        ]);
                    }


                    if (
                        (
                            $debit > 0
                            &&
                            $credit > 0
                        )
                        ||
                        (
                            $debit <= 0
                            &&
                            $credit <= 0
                        )
                    ) {
                        throw ValidationException::withMessages([
                            "lines.{$index}" => [
                                'Each journal line must contain either Debit or Credit, never both.',
                            ],
                        ]);
                    }


                    $account =
    ChartOfAccount::on('tenant')
        ->whereKey(
            (int) $line[
                'ledger_account_id'
            ]
        )
        ->where(
            'is_active',
            true
        )
        ->where(
            'allow_manual_posting',
            true
        )
        ->lockForUpdate()
        ->first();


                    if (! $account) {
                        throw ValidationException::withMessages([
                            "lines.{$index}.ledger_account_id" => [
                                'Selected ledger account is inactive, missing or is a non-posting group account.',
                            ],
                        ]);
                    }


                    return [

                        'account' =>
                            $account,

                        'debit' =>
                            $debit,

                        'credit' =>
                            $credit,

                        'memo' =>
                            isset(
                                $line[
                                    'memo'
                                ]
                            )
                            &&
                            trim(
                                (string) $line[
                                    'memo'
                                ]
                            ) !== ''
                                ? trim(
                                    (string) $line[
                                        'memo'
                                    ]
                                )
                                : null,
                    ];
                }
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Posting Accounts
    |--------------------------------------------------------------------------
    */

    public function postingAccounts(): array
    {
        return ChartOfAccount::on('tenant')
            ->where(
                'is_active',
                true
            )
            ->where(
                'allow_manual_posting',
                true
            )
            ->orderBy(
                'code'
            )
            ->get([
                'id',
                'code',
                'name',
                'account_type',
                'normal_balance',
            ])
            ->map(
                fn (
                    ChartOfAccount $account
                ): array => [

                    'id' =>
                        (int) $account->id,

                    'code' =>
                        $account->code,

                    'name' =>
                        $account->name,

                    'account_type' =>
                        $account->account_type,

                    'normal_balance' =>
                        $account->normal_balance,
                ]
            )
            ->values()
            ->all();
    }
}
