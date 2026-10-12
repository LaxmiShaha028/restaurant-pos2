<?php

namespace App\Services;
use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseOrderReceipt;
use App\Models\Supplier;
use Carbon\Carbon;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;



class BusinessOverviewService
{

    public function __construct(
        private readonly ProfitLossService $profitLossService
    ) {
    }


    public function getOverview(
        array $filters = []
    ): array {


        $fromDate =
    $filters['from_date']
    ??
    $filters['date_from']
    ??
    Carbon::now()
        ->startOfMonth()
        ->format('Y-m-d');


$toDate =
    $filters['to_date']
    ??
    $filters['date_to']
    ??
    Carbon::now()
        ->format('Y-m-d');
        /*
|--------------------------------------------------------------------------
| Profit Loss Summary
|--------------------------------------------------------------------------
*/


$profitLoss =

    $this->profitLossService
        ->getProfitLoss(
            [
                'from_date'=>$fromDate,

                'to_date'=>$toDate,
            ]
        );



$sales =

    $profitLoss['revenue']['total']
    ??
    0;



$totalExpenses =

    $profitLoss['expenses']['details'];


$foodCost =

    collect($totalExpenses)
        ->where(
            'account_type',
            'cogs'
        )
        ->sum(
            'balance'
        );


$expense =

    collect($totalExpenses)
        ->where(
            'account_type',
            'expense'
        )
        ->sum(
            'balance'
        );



$profit =

    $profitLoss['net_profit']
    ??
    0;

    /*
|--------------------------------------------------------------------------
| Purchase Summary
|--------------------------------------------------------------------------
*/


$purchase =

    DB::connection('tenant')
        ->table('purchase_order_receipt_items')
        ->whereBetween(
            'created_at',
            [
                $fromDate,
                Carbon::parse($toDate)
                    ->endOfDay()
            ]
        )
        ->sum('total_cost');

$salesSummary =

    $this->getSalesSummary(
        $fromDate,
        $toDate
    );


$purchaseReturn =

    $this->getPurchaseReturn(
        $fromDate,
        $toDate
    );

    $supplierDue =
    $this->getSupplierDue();

    $supplierDetails =

    $this->getSupplierDetails(
        $fromDate,
        $toDate
    );
    $supplier = [

    'details' => $supplierDetails,

];
    $cashPosition =

    $this->getCashPosition();

    $inventory =

    $this->getInventorySummary();

    $goodsReceived =
        $this->getGoodsReceived(
            $fromDate,
            $toDate
        );

/*
|--------------------------------------------------------------------------
| Supplier Summary
|--------------------------------------------------------------------------
*/

$purchaseOrders =

    PurchaseOrder::on('tenant')
        ->whereBetween(
            'order_date',
            [
                $fromDate,
                $toDate
            ]
        )
        ->sum(
            'total_amount'
        );


$supplierPaid =

    PurchaseOrderPayment::on('tenant')
        ->whereBetween(
            'payment_date',
            [
                $fromDate,
                $toDate
            ]
        )
        ->sum(
            'amount'
        );





        return [


            'period'=>[

                'from'=>$fromDate,

                'to'=>$toDate,

            ],



            'performance'=>[

    'sales'=>
        round(
            $sales,
            2
        ),


    'purchase'=>
    round(
        $purchase,
        2
    ),


    'food_cost'=>
        round(
            $foodCost,
            2
        ),


    'expense'=>
        round(
            $expense,
            2
        ),


    'profit'=>
        round(
            $profit,
            2
        ),

],



            'sales_summary'=>
                $salesSummary,



            'supplier'=>[

    'purchase_order'=>
    round(
        $purchaseOrders,
        2
    ),


    'goods_received'=>
        round(
            $goodsReceived,
            2
        ),


    'purchase_return'=>
        round(
            $purchaseReturn,
            2
        ),


    'paid'=>
        round(
            $supplierPaid,
            2
        ),


    'due'=>
        round(
            $supplierDue,
            2
        ),


    'details'=>
        $supplierDetails,

],


'inventory'=>
    $inventory,
            
            'cash_position'=>
                $cashPosition,



            'expense_breakdown' =>
    $this->getExpenseBreakdown(
        $fromDate,
        $toDate
    ),



            'food_cost_analysis' =>
                $this->getFoodCostAnalysis(
                    $sales,
                    $fromDate,
                    $toDate
                ),



            'alerts' =>
            $this->getAlerts(
                $supplier,
                $cashPosition,
                $inventory
            ),


        ];


    }

    private function getAlerts(
    array $supplier,
    array $cashPosition,
    array $inventory
): array {


    $alerts=[];


    // Supplier Due

    foreach(
        $supplier['details'] ?? []
        as $item
    ){

        if(
            ($item['due'] ?? 0) > 0
        ){

            $alerts[]=[

                'type'=>'warning',

                'title'=>'Supplier Due',

                'message'=>
                    $item['supplier_name']
                    .' has ৳'
                    .$item['due']
                    .' due'

            ];

        }

    }



    // Negative Wallet

    foreach(
        $cashPosition
        as $key=>$amount
    ){

        if(
            $amount < 0
        ){

            $alerts[]=[

                'type'=>'danger',

                'title'=>'Negative Balance',

                'message'=>
                    ucfirst($key)
                    .' balance is negative ৳'
                    .abs($amount)

            ];

        }

    }



    // Inventory

    if(
        ($inventory['low_stock'] ?? 0)>0
    ){

        $alerts[]=[

            'type'=>'warning',

            'title'=>'Low Stock',

            'message'=>
                $inventory['low_stock']
                .' items are below minimum stock'

        ];

    }



    if(
        ($inventory['out_of_stock'] ?? 0)>0
    ){

        $alerts[]=[

            'type'=>'danger',

            'title'=>'Out Of Stock',

            'message'=>
                $inventory['out_of_stock']
                .' items are out of stock'

        ];

    }



    return $alerts;

}
    private function getExpenseBreakdown(
    string $fromDate,
    string $toDate
): array {


    $accounts =
        ChartOfAccount::on('tenant')
            ->where(
                'account_type',
                'expense'
            )
            ->whereNotIn(
                'code',
                [
                    '6050',
                    '6810'
                ]
            )
            ->where(
                'is_active',
                true
            )
            ->get();



    $expenses=[];



    foreach($accounts as $account){


        $line =
            JournalLine::on('tenant')
                ->where(
                    'ledger_account_id',
                    $account->id
                )
                ->whereHas(
                    'journalEntry',
                    function($query) use(
                        $fromDate,
                        $toDate
                    ){

                        $query->whereBetween(
                            'journal_date',
                            [
                                $fromDate,
                                $toDate
                            ]
                        );

                    }
                )
                ->selectRaw(
                    '
                    SUM(debit) as debit,
                    SUM(credit) as credit
                    '
                )
                ->first();



        $amount =
            round(
                ($line->debit ?? 0)
                -
                ($line->credit ?? 0),
                2
            );



        if($amount <= 0){

            continue;

        }



        $expenses[]=[

            'account_id'=>$account->id,

            'account_name'=>$account->name,

            'amount'=>$amount,

        ];

    }



    return $expenses;

}

    private function getFoodCostAnalysis(
    float $sales,
    string $fromDate,
    string $toDate
): array {


    $cogsAccount =
        ChartOfAccount::on('tenant')
            ->where(
                'code',
                '5010'
            )
            ->first();



    if(!$cogsAccount){

        return [

            'amount'=>0,

            'percentage'=>0,

        ];

    }



    $line =
    JournalLine::on('tenant')
        ->where(
            'ledger_account_id',
            $cogsAccount->id
        )
        ->whereHas(
            'journalEntry',
            function ($query) use (
                $fromDate,
                $toDate
            ) {

                $query->whereBetween(
                    'journal_date',
                    [
                        $fromDate,
                        $toDate
                    ]
                );

            }
        )
        ->selectRaw(
            '
            SUM(debit) as debit,
            SUM(credit) as credit
            '
        )
        ->first();



    $foodCost =
        round(
            ($line->debit ?? 0)
            -
            ($line->credit ?? 0),
            2
        );



    $percentage =
        $sales > 0
            ?
            round(
                ($foodCost / $sales)
                *
                100,
                2
            )
            :
            0;



    return [

        'amount'=>$foodCost,

        'percentage'=>$percentage,

    ];

}

    private function getInventorySummary(): array
{

    $stocks = 
        \App\Models\WarehouseStock::on('tenant')
            ->with('rawMaterial')
            ->get();


    $stockValue = 0;

    $lowStock = 0;

    $outOfStock = 0;



    foreach($stocks as $stock){


        $quantity =
            (float)$stock->quantity;


        $cost =
            (float)$stock->average_unit_cost;



        $stockValue +=
            $quantity * $cost;



        if(
            $quantity <= 0
        ){

            $outOfStock++;

        }



        if(
            $stock->rawMaterial
            &&
            $quantity <
            (float)
            $stock->rawMaterial
                ->restaurant_minimum_quantity
        ){

            $lowStock++;

        }


    }



    return [

        'stock_value'=>
            round(
                $stockValue,
                2
            ),


        'low_stock'=>
            $lowStock,


        'out_of_stock'=>
            $outOfStock,

    ];

}

    /*
|--------------------------------------------------------------------------
| Cash Position
|--------------------------------------------------------------------------
*/

private function getCashPosition(): array
{

    $accounts =

        ChartOfAccount::on('tenant')

            ->whereIn(
                'system_key',
                [
                    'cash',
                    'bank',
                    'bkash_wallet',
                    'nagad_wallet',
                    'card_settlement'
                ]
            )

            ->get();



    $result = [

        'cash' => 0,

        'bank' => 0,

        'bkash' => 0,

        'nagad' => 0,

        'card' => 0,

    ];



    foreach($accounts as $account){


        $debit =

            JournalLine::on('tenant')

                ->where(
                    'ledger_account_id',
                    $account->id
                )

                ->sum(
                    'debit'
                );


        $credit =

            JournalLine::on('tenant')

                ->where(
                    'ledger_account_id',
                    $account->id
                )

                ->sum(
                    'credit'
                );



        $balance = round(
    $debit - $credit,
    2
);

        



        switch(
            $account->system_key
        ){

            case 'cash':

                $result['cash'] = $balance;

                break;


            case 'bank':

                $result['bank'] = $balance;

                break;


            case 'bkash_wallet':

                $result['bkash'] = $balance;

                break;


            case 'nagad_wallet':

                $result['nagad'] = $balance;

                break;


            case 'card_settlement':

                $result['card'] = $balance;

                break;

        }

    }


    return $result;

}

    /*
|--------------------------------------------------------------------------
| Sales Payment Summary
|--------------------------------------------------------------------------
*/

private function getSalesSummary(
    string $fromDate,
    string $toDate
): array {


    $orders =

        \App\Models\Order::on('tenant')

            ->whereBetween(
                'created_at',
                [
                    $fromDate . ' 00:00:00',

                    $toDate . ' 23:59:59'
                ]
            );


    return [

        'cash'=>

            round(
                (clone $orders)
                    ->where(
                        'payment_method',
                        'cash'
                    )
                    ->sum(
                        'paid_amount'
                    ),
                2
            ),



        'card'=>

            round(
                (clone $orders)
                    ->where(
                        'payment_method',
                        'card'
                    )
                    ->sum(
                        'paid_amount'
                    ),
                2
            ),



        'bkash'=>

            round(
                (clone $orders)
                    ->where(
                        'payment_method',
                        'bkash'
                    )
                    ->sum(
                        'paid_amount'
                    ),
                2
            ),



        'nagad'=>

            round(
                (clone $orders)
                    ->where(
                        'payment_method',
                        'nagad'
                    )
                    ->sum(
                        'paid_amount'
                    ),
                2
            ),



        'bank_transfer'=>

            round(
                (clone $orders)
                    ->where(
                        'payment_method',
                        'bank_transfer'
                    )
                    ->sum(
                        'paid_amount'
                    ),
                2
            ),



        'discount'=>

            round(
                (clone $orders)
                    ->sum(
                        'discount_amount'
                    ),
                2
            ),



        'service_charge'=>

            round(
                (clone $orders)
                    ->sum(
                        'service_charge'
                    ),
                2
            ),

    ];

}

    private function getSupplierDetails(
    string $fromDate,
    string $toDate
): array {


    $suppliers =

        Supplier::on('tenant')

            ->whereHas(
                'purchaseOrders',
                function($query) use(
                    $fromDate,
                    $toDate
                ){

                    $query->whereBetween(
                        'order_date',
                        [
                            $fromDate,
                            $toDate
                        ]
                    );

                }
            )

            ->get();



    $details = [];



    foreach(
        $suppliers as $supplier
    ){


        $received =

            PurchaseOrderReceipt::on('tenant')

                ->whereHas(
    'purchaseOrder',
    function($query) use(
        $supplier
    ){

        $query->where(
            'supplier_id',
            $supplier->id
        );

    }
)
->whereBetween(
    'received_at',
    [
        $fromDate,
        Carbon::parse($toDate)->endOfDay()
    ]
)

                ->with(
                    'items'
                )

                ->get()

                ->sum(
                    function($receipt){

                        return $receipt->items
                            ->sum('total_cost');

                    }
                );




        $paid =


            PurchaseOrderPayment::on('tenant')

                ->whereHas(
                    'purchaseOrder',
                    function($query) use(
                        $supplier,
                        $fromDate,
                        $toDate
                    ){

                        $query->where(
                            'supplier_id',
                            $supplier->id
                        );

                    }
                )

                ->whereBetween(
                    'payment_date',
                    [
                        $fromDate,
                        $toDate
                    ]
                )

                ->sum(
                    'amount'
                );





        $details[] = [

    'supplier_id'=>
        $supplier->id,


    'supplier_name'=>
        $supplier->supplier_name,


    'received'=>
        round(
            $received,
            2
        ),


    'paid'=>
        round(
            $paid,
            2
        ),


    'due'=>
        max(
            0,
            round(
                $received - $paid,
                2
            )
        ),


    'advance'=>
        max(
            0,
            round(
                $paid - $received,
                2
            )
        ),

];


    }


    return $details;

}

    private function getSupplierDue(): float
{

    $account =
        ChartOfAccount::on('tenant')
            ->where(
                'system_key',
                'accounts_payable'
            )
            ->first();


    if(!$account){
        return 0;
    }


    $credit =

        JournalLine::on('tenant')
            ->where(
                'ledger_account_id',
                $account->id
            )
            ->sum(
                'credit'
            );


    $debit =

        JournalLine::on('tenant')
            ->where(
                'ledger_account_id',
                $account->id
            )
            ->sum(
                'debit'
            );


    return round(
        $credit - $debit,
        2
    );

}

    /*
|--------------------------------------------------------------------------
| Supplier Goods Received
|--------------------------------------------------------------------------
*/

private function getGoodsReceived(
    string $fromDate,
    string $toDate
): float {


    return PurchaseOrderReceipt::on('tenant')

        ->whereDate(
    'received_at',
    '>=',
    $fromDate
)

->whereDate(
    'received_at',
    '<=',
    $toDate
)

        ->with(
            'items'
        )

        ->get()

        ->sum(

            function($receipt){


                return $receipt
    ->items
    ->sum(
        'total_cost'
    );


            }

        );


}

/*
|--------------------------------------------------------------------------
| Purchase Return
|--------------------------------------------------------------------------
*/

private function getPurchaseReturn(
    string $fromDate,
    string $toDate
): float {


    return JournalLine::on('tenant')

        ->whereHas(
            'journalEntry',
            function($query) use(
                $fromDate,
                $toDate
            ){

                $query->where(
                    'source_type',
                    'purchase_return'
                )

                ->whereBetween(
                    'journal_date',
                    [
                        $fromDate,
                        $toDate
                    ]
                );

            }
        )

        ->sum('credit');


}



}