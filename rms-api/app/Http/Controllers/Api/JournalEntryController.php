<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReverseJournalEntryRequest;
use App\Http\Requests\Api\StoreManualJournalRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use App\Services\JournalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JournalEntryController extends Controller
{
    public function __construct(
        private readonly JournalService $service
    ) {
    }


    public function index(
        Request $request
    ) {
        $this->ensureAdminOrManager(
            $request
        );


        $validated =
            $request->validate([

                'search' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'status' => [
                    'nullable',
                    Rule::in([
                        JournalEntry::STATUS_POSTED,
                        JournalEntry::STATUS_REVERSED,
                    ]),
                ],

                'entry_type' => [
                    'nullable',
                    Rule::in([
                        JournalEntry::TYPE_MANUAL,
                        JournalEntry::TYPE_SOURCE,
                        JournalEntry::TYPE_REVERSAL,
                    ]),
                ],

                'from_date' => [
                    'nullable',
                    'date',
                ],

                'to_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:from_date',
                    'before_or_equal:today',
                ],

                'page' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'per_page' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],
            ]);


        return JournalEntryResource::collection(
            $this->service
                ->getEntries(
                    $validated
                )
        )->additional([

            'success' =>
                true,

            'message' =>
                'Journal entries loaded successfully.',
        ]);
    }


    public function options(
        Request $request
    ): JsonResponse {

        $this->ensureAdminOrManager(
            $request
        );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Journal options loaded successfully.',

            'data' => [

                'accounts' =>
                    $this->service
                        ->postingAccounts(),

                'entry_types' => [
                    [
                        'value' =>
                            JournalEntry::TYPE_MANUAL,

                        'label' =>
                            'Manual',
                    ],
                    [
                        'value' =>
                            JournalEntry::TYPE_SOURCE,

                        'label' =>
                            'System Source',
                    ],
                    [
                        'value' =>
                            JournalEntry::TYPE_REVERSAL,

                        'label' =>
                            'Reversal',
                    ],
                ],

                'statuses' => [
                    [
                        'value' =>
                            JournalEntry::STATUS_POSTED,

                        'label' =>
                            'Posted',
                    ],
                    [
                        'value' =>
                            JournalEntry::STATUS_REVERSED,

                        'label' =>
                            'Reversed',
                    ],
                ],
            ],
        ]);
    }


    public function store(
        StoreManualJournalRequest $request
    ) {

        $entry =
            $this->service
                ->postManual(
                    $request->validated(),
                    $request->user()
                );


        return (
            new JournalEntryResource(
                $entry
            )
        )
            ->additional([

                'success' =>
                    true,

                'message' =>
                    'Balanced journal entry posted successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }


    public function show(
        Request $request,
        JournalEntry $journalEntry
    ) {

        $this->ensureAdminOrManager(
            $request
        );


        return (
            new JournalEntryResource(
                $this->service
                    ->showEntry(
                        $journalEntry
                    )
            )
        )->additional([

            'success' =>
                true,

            'message' =>
                'Journal entry loaded successfully.',
        ]);
    }


    public function reverse(
        ReverseJournalEntryRequest $request,
        JournalEntry $journalEntry
    ) {

        $reversal =
            $this->service
                ->reverse(
                    $journalEntry,
                    $request->validated()[
                        'reason'
                    ],
                    $request->validated()[
                        'reversal_date'
                    ],
                    $request->user()
                );


        return (
            new JournalEntryResource(
                $reversal
            )
        )->additional([

            'success' =>
                true,

            'message' =>
                'Journal entry reversed successfully. The original financial lines were preserved.',
        ]);
    }


    private function ensureAdminOrManager(
        Request $request
    ): void {

        $user =
            $request->user();


        $user?->loadMissing(
            'role'
        );


        $roleName =
            strtolower(
                trim(
                    (string) (
                        $user
                            ?->role
                            ?->name
                        ??
                        ''
                    )
                )
            );


        abort_unless(
            $user
            &&
            $user->is_active
            &&
            $user->blocked_at === null
            &&
            in_array(
                $roleName,
                [
                    'admin',
                    'manager',
                ],
                true
            ),
            403,
            'Only administrators and managers can access accounting journals.'
        );
    }
}
