<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BusinessOverviewService;

class BusinessOverviewController extends Controller
{
    public function __construct(
        private readonly BusinessOverviewService $service
    ) {
    }

    public function index(Request $request)
    {
        return response()->json(

            $this->service->getOverview([

                'from_date' => $request->date_from,

                'to_date' => $request->date_to,

            ])

        );
    }
}