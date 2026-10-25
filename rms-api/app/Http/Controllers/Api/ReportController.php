<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\DemandForecastService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\TrialBalanceService;
use App\Services\GeneralLedgerService;
use App\Services\ProfitLossService;
use App\Services\BalanceSheetService;
use App\Services\AccountingDashboardService;
use App\Services\OwnerLedgerService;
use App\Services\OwnerLedgerPdfService;
use App\Services\ProfitLossPdfService;
use App\Services\BalanceSheetPdfService;
use App\Services\TrialBalancePdfService;

class ReportController extends Controller
{
    public function __construct(
    private readonly ReportService $reportService,

    private readonly TrialBalanceService $trialBalanceService,

    private readonly GeneralLedgerService $generalLedgerService,

    private readonly ProfitLossService $profitLossService,

    private readonly BalanceSheetService $balanceSheetService,

    private readonly OwnerLedgerService $ownerLedgerService,

    private readonly OwnerLedgerPdfService $ownerLedgerPdfService,

    private readonly ProfitLossPdfService $profitLossPdfService,

    private readonly BalanceSheetPdfService $balanceSheetPdfService,
    private readonly TrialBalancePdfService $trialBalancePdfService,
) {
}


    /*
    |--------------------------------------------------------------------------
    | Orders Report
    |--------------------------------------------------------------------------
    */

    public function orders(
        Request $request
    ): JsonResponse {

        $orders =
            $this->reportService->getOrders(
                $request->all()
            );


        return $this->successResponse(
            $orders,
            'Orders report loaded successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Demand Forecast
    |--------------------------------------------------------------------------
    */

    public function demandForecast(
        Request $request,
        DemandForecastService $forecastService
    ): JsonResponse {

        $filters = $request->validate([
            'forecast_days' => ['sometimes', 'integer', 'in:1,7'],
        ]);

        return $this->successResponse(
            $forecastService->build((int) ($filters['forecast_days'] ?? 7)),
            'Demand forecast loaded successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Expenses Report
    |--------------------------------------------------------------------------
    */

    public function expenses(
        Request $request
    ): JsonResponse {

        $expenses =
            $this->reportService->getExpenses(
                $request->all()
            );


        return $this->successResponse(
            $expenses,
            'Expenses report loaded successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Expense Summary
    |--------------------------------------------------------------------------
    */

    public function expenseSummary(
        Request $request
    ): JsonResponse {

        return response()->json([

            'success' => true,

            'message' =>
                'Expense summary loaded successfully.',

            'data' =>
                $this->reportService->expenseSummary(
                    $request->all()
                ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Purchase Orders
    |--------------------------------------------------------------------------
    */

    public function purchaseOrders(
        Request $request
    ): JsonResponse {

        $purchaseOrders =
            $this->reportService->getPurchaseOrders(
                $request->all()
            );


        return $this->successResponse(
            $purchaseOrders,
            'Purchase report loaded successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Restaurant Stock
    |--------------------------------------------------------------------------
    */

    public function restaurantStock(
        Request $request
    ): JsonResponse {

        $stocks =
            $this->reportService->getRestaurantStock(
                $request->all()
            );


        return $this->successResponse(
            $stocks,
            'Restaurant stock loaded successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Warehouse Stock
    |--------------------------------------------------------------------------
    */

    public function warehouseStock(
        Request $request
    ): JsonResponse {

        $stocks =
            $this->reportService->getWarehouseStock(
                $request->all()
            );


        return $this->successResponse(
            $stocks,
            'Warehouse stock loaded successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Stock Transfers
    |--------------------------------------------------------------------------
    */

    public function stockTransfers(
        Request $request
    ): JsonResponse {

        $transfers =
            $this->reportService->getStockTransfers(
                $request->all()
            );


        return $this->successResponse(
            $transfers,
            'Stock transfer report loaded successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    public function attendance(
        Request $request
    ): JsonResponse {

        $attendance =
            $this->reportService->getAttendance(
                $request->all()
            );


        return $this->successResponse(
            $attendance,
            'Attendance report loaded successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination Response
    |--------------------------------------------------------------------------
    */

    private function successResponse(
        $data,
        string $message
    ): JsonResponse {

        if (
            $data instanceof LengthAwarePaginator
        ) {

            return response()->json([

                'success' => true,

                'message' => $message,

                'data' =>
                    $data->items(),

                'meta' => [

                    'current_page' =>
                        $data->currentPage(),

                    'last_page' =>
                        $data->lastPage(),

                    'per_page' =>
                        $data->perPage(),

                    'total' =>
                        $data->total(),

                    'from' =>
                        $data->firstItem(),

                    'to' =>
                        $data->lastItem(),

                ],

            ]);
        }


        return response()->json([

            'success' => true,

            'message' => $message,

            'data' => $data,

        ]);
    }
    /*
|--------------------------------------------------------------------------
| Trial Balance
|--------------------------------------------------------------------------
*/

public function trialBalance(
    Request $request
): JsonResponse {


    return response()->json([

        'success' => true,

        'message' =>
            'Trial balance loaded successfully.',

        'data' =>
            $this->trialBalanceService
                ->getTrialBalance(
                    $request->all()
                ),

    ]);

}
/*
|--------------------------------------------------------------------------
| Trial Balance PDF
|--------------------------------------------------------------------------
*/


public function trialBalancePdf(
    Request $request
)
{


    $data =

        $this->trialBalanceService

            ->getTrialBalance(

                $request->all()

            );



    $pdf =

        $this->trialBalancePdfService

            ->generate(

                $data

            );



    return $pdf->download(

        'trial-balance.pdf'

    );


}

 /*
|--------------------------------------------------------------------------
| Ledger Accounts
|--------------------------------------------------------------------------
*/

public function ledgerAccounts(): JsonResponse
{

    return response()->json([

        'success' => true,

        'message' =>
            'Ledger accounts loaded successfully.',

        'data' =>
            $this->generalLedgerService
                ->getAccounts(),

    ]);

}
/*
|--------------------------------------------------------------------------
| General Ledger
|--------------------------------------------------------------------------
*/

public function generalLedger(
    Request $request
): JsonResponse {


    $accountId =
        (int)
        $request->account_id;


    return response()->json([

        'success' => true,

        'message' =>
            'General ledger loaded successfully.',

        'data' =>
            $this->generalLedgerService
                ->getLedger(
                    $accountId,
                    $request->all()
                ),

    ]);

}
/*
|--------------------------------------------------------------------------
| Profit Loss
|--------------------------------------------------------------------------
*/


public function profitLoss(
    Request $request
): JsonResponse {


    return response()->json([

        'success' => true,

        'message' =>
            'Profit loss report loaded successfully.',

        'data' =>
            $this->profitLossService
                ->getProfitLoss(
                    $request->all()
                ),

    ]);

}

public function accountingDashboard(
    Request $request,
    AccountingDashboardService $service
){

    $data =

        $service->getDashboard(

            $request->all()

        );


    return response()->json([

        'success'=>true,

        'message'=>'Accounting dashboard loaded successfully.',

        'data'=>$data

    ]);

}
/*
|--------------------------------------------------------------------------
| Owner Ledger
|--------------------------------------------------------------------------
*/


public function ownerLedger(
    Request $request
): JsonResponse {


    return response()->json([


        'success' => true,


        'message' =>
            'Owner ledger loaded successfully.',


        'data' =>

            $this->ownerLedgerService

                ->getOwnerLedger(

                    $request->all()

                ),


    ]);

}
/*
|--------------------------------------------------------------------------
| Owner Ledger PDF
|--------------------------------------------------------------------------
*/

public function ownerLedgerPdf(
    Request $request
) {


    $data =

        $this->ownerLedgerService

            ->getOwnerLedger(

                $request->all()

            );



    $pdf =

        $this->ownerLedgerPdfService

            ->generate(

                $data

            );



    return $pdf->download(

        'owner-ledger-report.pdf'

    );


}

public function profitLossPdf(
    Request $request
)
{

    $data =

        $this->profitLossService

            ->getProfitLoss(

                $request->all()

            );



    $pdf =

        $this->profitLossPdfService

            ->generate(

                $data

            );


    return $pdf->download(

        'profit-loss.pdf'

    );


}
/*
|--------------------------------------------------------------------------
| Balance Sheet
|--------------------------------------------------------------------------
*/

public function balanceSheet(
    Request $request
): JsonResponse {


    return response()->json([

        'success' => true,

        'message' =>
            'Balance sheet loaded successfully.',

        'data' =>
            $this->balanceSheetService
                ->getBalanceSheet(
                    $request->all()
                ),

    ]);

}
/*
|--------------------------------------------------------------------------
| Balance Sheet PDF
|--------------------------------------------------------------------------
*/

public function balanceSheetPdf(
    Request $request
)
{


    $data =

        $this->balanceSheetService

            ->getBalanceSheet(

                $request->all()

            );



    $pdf =

        $this->balanceSheetPdfService

            ->generate(

                $data

            );



    return $pdf->download(

        'balance-sheet.pdf'

    );


}
}
