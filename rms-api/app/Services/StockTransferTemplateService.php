<?php

namespace App\Services;

use App\Models\RawMaterial;
use App\Models\StockTransferTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class StockTransferTemplateService
{
    public function getTemplates(
        array $filters = []
    ): Collection {

        $query =
            StockTransferTemplate::query()

                ->with([
                    'items.rawMaterial',
                    'creator',
                    'updater',
                ]);


        if (
            array_key_exists(
                'active',
                $filters
            )
            &&
            $filters[
                'active'
            ]
            !==
            null
        ) {

            $query->where(
                'is_active',
                (bool) $filters[
                    'active'
                ]
            );
        }


        $search =
            trim(
                (string) (
                    $filters[
                        'search'
                    ]
                    ??
                    ''
                )
            );


        if (
            $search !== ''
        ) {

            $query->where(
                static function (
                    $searchQuery
                ) use (
                    $search
                ): void {

                    $searchQuery

                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhereHas(
                            'items',
                            static function (
                                $itemQuery
                            ) use (
                                $search
                            ): void {

                                $itemQuery->where(
                                    'item_name',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        );
                }
            );
        }


        return $query

            ->orderByDesc(
                'is_active'
            )

            ->orderBy(
                'name'
            )

            ->get();
    }


    public function getTemplate(
        int $templateId
    ): StockTransferTemplate {

        return StockTransferTemplate::query()

            ->with([
                'items.rawMaterial',
                'creator',
                'updater',
            ])

            ->findOrFail(
                $templateId
            );
    }


    public function createTemplate(
        array $data,
        User $user
    ): StockTransferTemplate {

        return DB::transaction(

            function () use (
                $data,
                $user
            ): StockTransferTemplate {

                $template =
                    StockTransferTemplate::create([

                        'name' =>
                            trim(
                                (string) $data[
                                    'name'
                                ]
                            ),

                        'description' =>
                            $this->cleanText(
                                $data[
                                    'description'
                                ]
                                ??
                                null
                            ),

                        'is_active' =>
                            array_key_exists(
                                'is_active',
                                $data
                            )
                                ? (bool) $data[
                                    'is_active'
                                ]
                                : true,

                        'created_by' =>
                            $user->id,

                        'updated_by' =>
                            $user->id,

                    ]);


                $this->replaceItems(
                    template:
                        $template,

                    items:
                        $data[
                            'items'
                        ]
                        ??
                        []
                );


                return $this->getTemplate(
                    $template->id
                );
            },

            3
        );
    }


    public function updateTemplate(
        int $templateId,
        array $data,
        User $user
    ): StockTransferTemplate {

        return DB::transaction(

            function () use (
                $templateId,
                $data,
                $user
            ): StockTransferTemplate {

                $template =
                    StockTransferTemplate::query()

                        ->lockForUpdate()

                        ->findOrFail(
                            $templateId
                        );


                $template->update([

                    'name' =>
                        trim(
                            (string) $data[
                                'name'
                            ]
                        ),

                    'description' =>
                        $this->cleanText(
                            $data[
                                'description'
                            ]
                            ??
                            null
                        ),

                    'is_active' =>
                        array_key_exists(
                            'is_active',
                            $data
                        )
                            ? (bool) $data[
                                'is_active'
                            ]
                            : (bool) $template
                                ->is_active,

                    'updated_by' =>
                        $user->id,

                ]);


                $this->replaceItems(
                    template:
                        $template,

                    items:
                        $data[
                            'items'
                        ]
                        ??
                        []
                );


                return $this->getTemplate(
                    $template->id
                );
            },

            3
        );
    }


    public function setStatus(
        int $templateId,
        bool $isActive,
        User $user
    ): StockTransferTemplate {

        $template =
            StockTransferTemplate::query()
                ->findOrFail(
                    $templateId
                );


        $template->update([

            'is_active' =>
                $isActive,

            'updated_by' =>
                $user->id,

        ]);


        return $this->getTemplate(
            $template->id
        );
    }


    public function deleteTemplate(
        int $templateId
    ): void {

        $template =
            StockTransferTemplate::query()
                ->findOrFail(
                    $templateId
                );


        $template->delete();
    }


    private function replaceItems(
        StockTransferTemplate $template,
        array $items
    ): void {

        if (
            $items === []
        ) {

            throw ValidationException::withMessages([

                'items' => [
                    'Add at least one raw material to the transfer template.',
                ],

            ]);
        }


        $materialIds =
            collect(
                $items
            )
                ->pluck(
                    'raw_material_id'
                )
                ->map(
                    static fn (
                        mixed $id
                    ): int =>
                        (int) $id
                )
                ->values();


        if (
            $materialIds->count()
            !==
            $materialIds
                ->unique()
                ->count()
        ) {

            throw ValidationException::withMessages([

                'items' => [
                    'The same raw material cannot appear more than once in a template.',
                ],

            ]);
        }


        $rawMaterials =
            RawMaterial::query()

                ->whereIn(
                    'id',
                    $materialIds
                )

                ->whereNull(
                    'deleted_at'
                )

                ->where(
                    'is_active',
                    true
                )

                ->get()

                ->keyBy(
                    'id'
                );


        if (
            $rawMaterials->count()
            !==
            $materialIds
                ->unique()
                ->count()
        ) {

            throw ValidationException::withMessages([

                'items' => [
                    'One or more raw materials are unavailable, inactive, or deleted.',
                ],

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Templates Are Editable Configuration
        |--------------------------------------------------------------------------
        |
        | Template items are not accounting history. Replacing them is safe.
        | Actual transfers continue to be immutable and are still created only
        | through StockTransferService.
        |
        */

        $template
            ->items()
            ->delete();


        foreach (
            array_values(
                $items
            )
            as
            $index => $item
        ) {

            $rawMaterial =
                $rawMaterials->get(
                    (int) $item[
                        'raw_material_id'
                    ]
                );


            $quantity =
                round(
                    (float) $item[
                        'quantity'
                    ],
                    4
                );


            if (
                $quantity <= 0
            ) {

                throw ValidationException::withMessages([

                    "items.{$index}.quantity" => [
                        'Default transfer quantity must be greater than zero.',
                    ],

                ]);
            }


            $template
                ->items()
                ->create([

                    'raw_material_id' =>
                        $rawMaterial->id,

                    'item_name' =>
                        $rawMaterial
                            ->material_name,

                    'unit' =>
                        $rawMaterial
                            ->base_unit,

                    'quantity' =>
                        $quantity,

                    'notes' =>
                        $this->cleanText(
                            $item[
                                'notes'
                            ]
                            ??
                            null
                        ),

                    'sort_order' =>
                        $index,

                ]);
        }
    }


    private function cleanText(
        mixed $value
    ): ?string {

        if (
            $value ===
            null
        ) {
            return null;
        }


        $value =
            trim(
                (string) $value
            );


        return $value !== ''
            ? $value
            : null;
    }
}
