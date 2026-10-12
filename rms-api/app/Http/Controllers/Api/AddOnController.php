<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddOnRequest;
use App\Http\Requests\UpdateAddOnRequest;
use App\Http\Resources\AddOnResource;
use App\Models\AddOn;
use App\Traits\ApiResponse;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AddOnController extends Controller
{
    use ApiResponse, HasAuditLog;

    /**
     * Display a paginated listing of add-ons.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            max((int) $request->input('per_page', 10), 1),
            100
        );

        $search = trim(
            (string) $request->input('search', '')
        );

        $status = $request->input('status');

        $query = AddOn::query();

        if ($search !== '') {
            $query->where(
                function (Builder $builder) use ($search): void {
                    $builder
                        ->where(
                            'add_on_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if (
            $status === 'available' ||
            $status === '1' ||
            $status === 1
        ) {
            $query->where('is_available', true);
        }

        if (
            $status === 'unavailable' ||
            $status === '0' ||
            $status === 0
        ) {
            $query->where('is_available', false);
        }

        $addOns = $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginatedResponse(
            AddOnResource::collection($addOns),
            $addOns,
            'Add-ons retrieved successfully.'
        );
    }

    /**
     * Store a newly created add-on.
     */
    public function store(
        StoreAddOnRequest $request
    ): JsonResponse {
        try {
            $addOn = DB::transaction(
                function () use ($request): AddOn {
                    return AddOn::create(
                        $request->validated()
                    );
                }
            );
            $this->audit(

    'addons',

    'created',

    $addOn,

    null,

    $addOn->toArray(),

    'Created add-on: '.$addOn->add_on_name

);

            return $this->createdResponse(
                new AddOnResource($addOn),
                'Add-on created successfully.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                'Unable to create add-on.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Display the specified add-on.
     */
    public function show(AddOn $addOn): JsonResponse
    {
        return $this->successResponse(
            new AddOnResource($addOn),
            'Add-on retrieved successfully.'
        );
    }

    /**
     * Update the specified add-on.
     */
    public function update(
        UpdateAddOnRequest $request,
        AddOn $addOn
    ): JsonResponse {
        $oldData = $addOn->toArray();
        try {
            DB::transaction(
                function () use ($request, $addOn): void {
                    $addOn->update(
                        $request->validated()
                    );
                }
            );

            $addOn->refresh();
            $this->audit(

    'addons',

    'updated',

    $addOn,

    $oldData,

    $addOn->fresh()->toArray(),

    'Updated add-on: '.$addOn->add_on_name

);

            return $this->updatedResponse(
                new AddOnResource($addOn),
                'Add-on updated successfully.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                'Unable to update add-on.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Remove the specified add-on.
     */
    public function destroy(AddOn $addOn): JsonResponse
    {
        $oldData = $addOn->toArray();
        try {
            DB::transaction(
                function () use ($addOn): void {
                    $addOn->delete();
                }
            );
            $this->audit(

    'addons',

    'deleted',

    $addOn,

    $oldData,

    null,

    'Deleted add-on: '.$addOn->add_on_name

);

            return $this->deletedResponse(
                'Add-on deleted successfully.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                'Unable to delete add-on.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Toggle add-on availability.
     */
    public function toggleStatus(AddOn $addOn): JsonResponse
    {
        $oldStatus = $addOn->is_available;
        try {
            $addOn->update([
                'is_available' => !$addOn->is_available,
            ]);

            $addOn->refresh();

            $this->audit(

    'addons',

    'status_changed',

    $addOn,

    [
        'is_available'=>$oldStatus
    ],

    [
        'is_available'=>$addOn->is_available
    ],

    'Changed add-on availability: '.$addOn->add_on_name

);

            return $this->updatedResponse(
                new AddOnResource($addOn),
                $addOn->is_available
                    ? 'Add-on is now available.'
                    : 'Add-on is now unavailable.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                'Unable to update add-on availability.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}