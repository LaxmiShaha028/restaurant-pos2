<template>

<section>

    <!-- Header -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                Business Overview
            </h4>

            <small class="text-muted">
                Complete restaurant performance summary
            </small>

        </div>

    </div>



    <!-- Loading -->

    <div
        v-if="loading"
        class="text-center py-5"
    >

        Loading Business Overview...

    </div>



    <!-- Data -->

    <div v-else>


        <!-- ========================================================= -->
        <!-- KPI SUMMARY -->
        <!-- ========================================================= -->

        <div class="row g-3 mb-4">


            <!-- Sales -->

            <div class="col-xl-3 col-md-6">

                <div class="card h-100 border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <small class="hello">
                                    Total Sales
                                </small>

                                <h4
    class="fw-bold mt-2 mb-1"
    :class="
        Number(overview.performance?.sales || 0) > 0
            ? 'text-success'
            : 'text-muted'
    "
>
    {{ money(overview.performance?.sales) }}
</h4>

                                <small class="text-muted">
                                    Sales revenue
                                </small>

                            </div>

                            <div class="bg-success-subtle rounded p-2">

                                <i
                                    class="bi bi-cart-check text-success fs-5"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Purchase -->

            <div class="col-xl-3 col-md-6">

                <div class="card h-100 border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <small class="text-muted">
                                    Purchase
                                </small>

                                <h4 class="fw-bold mt-2 mb-1">
                                    {{ money(overview.performance?.purchase) }}
                                </h4>

                                <small class="text-muted">
                                    Goods received
                                </small>

                            </div>

                            <div class="bg-primary-subtle rounded p-2">

                                <i
                                    class="bi bi-box-seam text-primary fs-5"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Profit -->

            <div class="col-xl-3 col-md-6">

                <div class="card h-100 border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <small class="text-muted">
                                    Net Profit
                                </small>

                                <h4
                                    class="fw-bold mt-2 mb-1 text-success"
                                >
                                    {{ money(overview.performance?.profit) }}
                                </h4>

                                <small class="text-muted">
                                    After expenses
                                </small>

                            </div>

                            <div class="bg-success-subtle rounded p-2">

                                <i
                                    class="bi bi-graph-up-arrow text-success fs-5"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Stock Value -->

            <div class="col-xl-3 col-md-6">

                <div class="card h-100 border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <small class="text-muted">
                                    Stock Value
                                </small>

                                <h4 class="fw-bold mt-2 mb-1">
                                    {{ money(overview.inventory?.stock_value) }}
                                </h4>

                                <small class="text-muted">
                                    Current inventory value
                                </small>

                            </div>

                            <div class="bg-dark-subtle rounded p-2">

                                <i
                                    class="bi bi-boxes text-dark fs-5"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


        </div>



        <!-- ========================================================= -->
        <!-- FINANCIAL SUMMARY -->
        <!-- ========================================================= -->

        <div class="row g-3 mb-4">


            <!-- Food Cost -->

            <div class="col-xl-3 col-md-6">

                <div class="card h-100 border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <small class="text-muted">
                                    Food Cost
                                </small>

                                <h5 class="fw-bold mt-2 mb-1">

                                    {{
                                        money(
                                            overview.performance?.food_cost
                                        )
                                    }}

                                </h5>

                                <small class="text-danger">

                                    {{
                                        overview.food_cost_analysis?.percentage || 0
                                    }}%

                                    of sales

                                </small>

                            </div>

                            <div class="bg-danger-subtle rounded p-2">

                                <i
                                    class="bi bi-egg-fried text-danger fs-5"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Operating Expense -->

            <div class="col-xl-3 col-md-6">

                <div class="card h-100 border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <small class="text-muted">
                                    Operating Expense
                                </small>

                                <h5 class="fw-bold mt-2 mb-1">

                                    {{
                                        money(
                                            overview.performance?.expense
                                        )
                                    }}

                                </h5>

                                <small class="text-muted">
                                    Total business expenses
                                </small>

                            </div>

                            <div class="bg-warning-subtle rounded p-2">

                                <i
                                    class="bi bi-receipt text-warning fs-5"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Supplier Due -->

            <div class="col-xl-3 col-md-6">

                <div
                    class="card h-100 border-0 shadow-sm"
                    :class="
                        Number(overview.supplier?.due || 0) > 0
                            ? 'border-start border-4 border-danger'
                            : 'border-start border-4 border-success'
                    "
                >

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <small class="text-muted">
                                    Supplier Due
                                </small>

                                <h5
                                    class="fw-bold mt-2 mb-1"
                                    :class="
                                        Number(
                                            overview.supplier?.due || 0
                                        ) > 0
                                            ? 'text-danger'
                                            : 'text-success'
                                    "
                                >

                                    {{
                                        money(
                                            overview.supplier?.due
                                        )
                                    }}

                                </h5>

                                <small class="text-muted">
                                    Amount payable to suppliers
                                </small>

                            </div>

                            <div class="bg-danger-subtle rounded p-2">

                                <i
                                    class="bi bi-person-exclamation text-danger fs-5"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Cash Position -->

            <div class="col-xl-3 col-md-6">

                <div class="card h-100 border-0 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <small class="text-muted">
                                    Cash Position
                                </small>

                                <h5 class="fw-bold mt-2 mb-1">

                                    {{
                                        money(
                                            Number(
                                                overview.cash_position?.cash || 0
                                            ) +

                                            Number(
                                                overview.cash_position?.bank || 0
                                            ) +

                                            Number(
                                                overview.cash_position?.bkash || 0
                                            ) +

                                            Number(
                                                overview.cash_position?.nagad || 0
                                            ) +

                                            Number(
                                                overview.cash_position?.card || 0
                                            )
                                        )
                                    }}

                                </h5>

                                <small class="text-muted">
                                    Cash + bank + wallets
                                </small>

                            </div>

                            <div class="bg-info-subtle rounded p-2">

                                <i
                                    class="bi bi-wallet2 text-info fs-5"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


        </div>



        <!-- ========================================================= -->
<!-- SALES PAYMENT BREAKDOWN -->
<!-- ========================================================= -->

<div class="row g-3 mb-4">

    <div class="col-12">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h6 class="fw-bold mb-1">
                            Sales Payment Breakdown
                        </h6>

                        <small class="text-muted">
                            Sales collected by payment method
                        </small>

                    </div>

                    <i class="bi bi-credit-card-2-front text-primary fs-4"></i>

                </div>


                <div class="row g-3">


                    <!-- Cash -->

                    <div class="col-xl col-md-4 col-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                Cash
                            </small>

                            <h6 class="fw-bold mt-2 mb-0">

                                {{
                                    money(
                                        overview.sales_summary?.cash
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- Card -->

                    <div class="col-xl col-md-4 col-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                Card
                            </small>

                            <h6 class="fw-bold mt-2 mb-0">

                                {{
                                    money(
                                        overview.sales_summary?.card
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- bKash -->

                    <div class="col-xl col-md-4 col-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                bKash
                            </small>

                            <h6 class="fw-bold mt-2 mb-0">

                                {{
                                    money(
                                        overview.sales_summary?.bkash
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- Nagad -->

                    <div class="col-xl col-md-4 col-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                Nagad
                            </small>

                            <h6 class="fw-bold mt-2 mb-0">

                                {{
                                    money(
                                        overview.sales_summary?.nagad
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- Bank -->

                    <div class="col-xl col-md-4 col-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                Bank Transfer
                            </small>

                            <h6 class="fw-bold mt-2 mb-0">

                                {{
                                    money(
                                        overview.sales_summary?.bank_transfer
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- Discount -->

                    <div class="col-xl col-md-4 col-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                Discount
                            </small>

                            <h6 class="fw-bold mt-2 mb-0 text-warning">

                                {{
                                    money(
                                        overview.sales_summary?.discount
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- Service Charge -->

                    <div class="col-xl col-md-4 col-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                Service Charge
                            </small>

                            <h6 class="fw-bold mt-2 mb-0 text-info">

                                {{
                                    money(
                                        overview.sales_summary?.service_charge
                                    )
                                }}

                            </h6>

                        </div>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>



<!-- ========================================================= -->
<!-- SUPPLIER OVERVIEW -->
<!-- ========================================================= -->

<div class="row g-3 mb-4">

    <div class="col-12">

        <div class="card border-0 shadow-sm">

            <div class="card-body">


                <!-- Header -->

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h6 class="fw-bold mb-1">
                            Supplier Overview
                        </h6>

                        <small class="text-muted">
                            Purchase, payment and outstanding supplier balance
                        </small>

                    </div>

                    <i class="bi bi-truck text-primary fs-4"></i>

                </div>

                



                <!-- Supplier Summary -->

                <div class="row g-3 mb-4">


                    <!-- Purchase Order -->

                    <div class="col-xl-3 col-md-6">

                        <div class="bg-light rounded p-3">

                            <small class="text-muted">
                                Purchase Orders
                            </small>

                            <h6 class="fw-bold mt-2 mb-0">

                                {{
                                    money(
                                        overview.supplier?.purchase_order
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- Goods Received -->

                    <div class="col-xl-3 col-md-6">

                        <div class="bg-light rounded p-3">

                            <small class="text-muted">
                                Goods Received
                            </small>

                            <h6 class="fw-bold mt-2 mb-0">

                                {{
                                    money(
                                        overview.supplier?.goods_received
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- Purchase Return -->

                    <div class="col-xl-3 col-md-6">

                        <div class="bg-light rounded p-3">

                            <small class="text-muted">
                                Purchase Return
                            </small>

                            <h6 class="fw-bold mt-2 mb-0 text-warning">

                                {{
                                    money(
                                        overview.supplier?.purchase_return
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- Paid -->

                    <div class="col-xl-3 col-md-6">

                        <div class="bg-light rounded p-3">

                            <small class="text-muted">
                                Supplier Paid
                            </small>

                            <h6 class="fw-bold mt-2 mb-0 text-success">

                                {{
                                    money(
                                        overview.supplier?.paid
                                    )
                                }}

                            </h6>

                        </div>

                    </div>


                </div>



                <!-- Supplier Table -->

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>
                                    Supplier
                                </th>

                                <th class="text-end">
                                    Received
                                </th>

                                <th class="text-end">
                                    Paid
                                </th>

                                <th class="text-end">
                                    Due
                                </th>

                                <th class="text-end">
                                    Advance
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <tr
                                v-for="supplier in overview.supplier?.details || []"
                                :key="supplier.supplier_id"
                            >

                                <td>

                                    <div class="fw-semibold">
                                        {{ supplier.supplier_name }}
                                    </div>

                                </td>


                                <td class="text-end">

                                    {{
                                        money(
                                            supplier.received
                                        )
                                    }}

                                </td>


                                <td class="text-end text-success">

                                    {{
                                        money(
                                            supplier.paid
                                        )
                                    }}

                                </td>


                                <td
                                    class="text-end fw-semibold"
                                    :class="
                                        Number(supplier.due || 0) > 0
                                            ? 'text-danger'
                                            : 'text-success'
                                    "
                                >

                                    {{
                                        money(
                                            supplier.due
                                        )
                                    }}

                                </td>


                                <td class="text-end text-primary">

                                    {{
                                        money(
                                            supplier.advance
                                        )
                                    }}

                                </td>

                            </tr>


                            <!-- Empty -->

                            <tr
                                v-if="
                                    !overview.supplier?.details ||
                                    overview.supplier.details.length === 0
                                "
                            >

                                <td
                                    colspan="5"
                                    class="text-center text-muted py-4"
                                >

                                    No supplier transactions found.

                                </td>

                            </tr>


                        </tbody>

                    </table>

                </div>


            </div>

        </div>

    </div>

</div>

<!-- ========================================================= -->
<!-- INVENTORY + CASH POSITION -->
<!-- ========================================================= -->

<div class="row g-3 mb-4">


    <!-- ===================================================== -->
    <!-- INVENTORY OVERVIEW -->
    <!-- ===================================================== -->

    <div class="col-xl-6">

        <div class="card h-100 border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h6 class="fw-bold mb-1">
                            Inventory Overview
                        </h6>

                        <small class="text-muted">
                            Current stock position
                        </small>

                    </div>

                    <i class="bi bi-boxes text-primary fs-4"></i>

                </div>


                <div class="row g-3">


                    <!-- Stock Value -->

                    <div class="col-12">

                        <div class="border rounded p-3">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">
                                        Total Stock Value
                                    </small>

                                    <h4 class="fw-bold mt-2 mb-0">

                                        {{
                                            money(
                                                overview.inventory?.stock_value
                                            )
                                        }}

                                    </h4>

                                </div>

                                <i
                                    class="bi bi-box-seam text-primary fs-3"
                                ></i>

                            </div>

                        </div>

                    </div>



                    <!-- Low Stock -->

                    <div class="col-md-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                Low Stock Items
                            </small>

                            <h5
                                class="fw-bold mt-2 mb-0"
                                :class="
                                    Number(
                                        overview.inventory?.low_stock || 0
                                    ) > 0
                                        ? 'text-warning'
                                        : 'text-success'
                                "
                            >

                                {{
                                    overview.inventory?.low_stock || 0
                                }}

                            </h5>

                            <small class="text-muted">
                                Need attention
                            </small>

                        </div>

                    </div>



                    <!-- Out of Stock -->

                    <div class="col-md-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                Out of Stock
                            </small>

                            <h5
                                class="fw-bold mt-2 mb-0"
                                :class="
                                    Number(
                                        overview.inventory?.out_of_stock || 0
                                    ) > 0
                                        ? 'text-danger'
                                        : 'text-success'
                                "
                            >

                                {{
                                    overview.inventory?.out_of_stock || 0
                                }}

                            </h5>

                            <small class="text-muted">
                                Unavailable items
                            </small>

                        </div>

                    </div>


                </div>

            </div>

        </div>

    </div>



    <!-- ===================================================== -->
    <!-- CASH POSITION -->
    <!-- ===================================================== -->

    <div class="col-xl-6">

        <div class="card h-100 border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h6 class="fw-bold mb-1">
                            Cash Position
                        </h6>

                        <small class="text-muted">
                            Current balance by account
                        </small>

                    </div>

                    <i class="bi bi-wallet2 text-success fs-4"></i>

                </div>


                <div class="row g-3">


                    <!-- Cash -->

                    <div class="col-md-6">

                        <div class="border rounded p-3">

                            <small class="text-muted">
                                Cash in Hand
                            </small>

                            <h6 class="fw-bold mt-2 mb-0">

                                {{
                                    money(
                                        overview.cash_position?.cash
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- Bank -->

                    <div class="col-md-6">

                        <div class="border rounded p-3">

                            <small class="text-muted">
                                Bank Account
                            </small>

                            <h6 class="fw-bold mt-2 mb-0">

                                {{
                                    money(
                                        overview.cash_position?.bank
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- bKash -->

                    <div class="col-md-6">

                        <div class="border rounded p-3">

                            <small class="text-muted">
                                bKash
                            </small>

                            <h6
    class="fw-bold mt-2 mb-1"
    :class="
        Number(
            overview.cash_position?.bkash || 0
        ) < 0
            ? 'text-danger'
            : 'text-success'
    "
>
    {{
        money(
            overview.cash_position?.bkash
        )
    }}
</h6>

<small
    v-if="
        Number(
            overview.cash_position?.bkash || 0
        ) < 0
    "
    class="text-danger fw-semibold"
>
    Negative balance
</small>

<small
    v-else
    class="text-muted"
>
    Available balance
</small>

                        </div>

                    </div>



                    <!-- Nagad -->

                    <div class="col-md-6">

                        <div class="border rounded p-3">

                            <small class="text-muted">
                                Nagad
                            </small>

                            <h6 class="fw-bold mt-2 mb-0">

                                {{
                                    money(
                                        overview.cash_position?.nagad
                                    )
                                }}

                            </h6>

                        </div>

                    </div>



                    <!-- Card -->

                    <div class="col-12">

                        <div class="border rounded p-3">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">
                                        Card Settlement
                                    </small>

                                    <h6 class="fw-bold mt-2 mb-0">

                                        {{
                                            money(
                                                overview.cash_position?.card
                                            )
                                        }}

                                    </h6>

                                </div>

                                <i
                                    class="bi bi-credit-card text-primary fs-4"
                                ></i>

                            </div>

                        </div>

                    </div>


                </div>

            </div>

        </div>

    </div>


</div>



<!-- ========================================================= -->
<!-- ALERTS -->
<!-- ========================================================= -->

<div class="row g-3 mb-4">

    <div class="col-12">

        <div class="card border-0 shadow-sm">

            <div class="card-body">


                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h6 class="fw-bold mb-1">
                            Business Alerts
                        </h6>

                        <small class="text-muted">
                            Important items that need your attention
                        </small>

                    </div>

                    <i class="bi bi-bell text-warning fs-4"></i>

                </div>



                <!-- Alerts -->

                <div
                    v-if="
                        overview.alerts &&
                        overview.alerts.length
                    "
                >

                    <div
                        v-for="(alert, index) in overview.alerts"
                        :key="index"
                        class="d-flex align-items-start gap-3 border rounded p-3 mb-2"
                    >

                        <div
                            class="rounded-circle p-2"
                            :class="
                                alert.type === 'danger'
                                    ? 'bg-danger-subtle text-danger'
                                    : alert.type === 'warning'
                                        ? 'bg-warning-subtle text-warning'
                                        : 'bg-info-subtle text-info'
                            "
                        >

                            <i
                                class="bi"
                                :class="
                                    alert.type === 'danger'
                                        ? 'bi-exclamation-octagon'
                                        : alert.type === 'warning'
                                            ? 'bi-exclamation-triangle'
                                            : 'bi-info-circle'
                                "
                            ></i>

                        </div>


                        <div>

                            <div class="fw-semibold">
                                {{ alert.title }}
                            </div>

                            <small class="text-muted">
                                {{ alert.message }}
                            </small>

                        </div>

                    </div>

                </div>



                <!-- No Alerts -->

                <div
                    v-else
                    class="text-center text-muted py-4"
                >

                    <i class="bi bi-check-circle fs-3 text-success"></i>

                    <div class="mt-2">
                        No business alerts
                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


    </div>


</section>

</template>



<script setup>

import {
    ref,
    watch,
} from 'vue'

import reportService from '@/services/reportService'


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading = ref(false)


/*
|--------------------------------------------------------------------------
| Business Overview Data
|--------------------------------------------------------------------------
*/

const overview = ref({})


/*
|--------------------------------------------------------------------------
| Receive Filters From Reports Management
|--------------------------------------------------------------------------
*/

const props = defineProps({

    filters: {
        type: Object,
        default: () => ({
            date_from: '',
            date_to: '',
        }),
    },

})


/*
|--------------------------------------------------------------------------
| Load Business Overview
|--------------------------------------------------------------------------
*/

async function loadOverview(filters = {}) {

    loading.value = true

    console.log(
        'BUSINESS OVERVIEW API FILTER:',
        filters
    )

    try {

        const params = {
            date_from: filters.date_from || '',
            date_to: filters.date_to || '',
        }


        console.log(
            'BUSINESS OVERVIEW REQUEST PARAMS:',
            params
        )


        const response =
            await reportService.getBusinessOverview(
                params
            )


        console.log(
            'BUSINESS OVERVIEW RESPONSE:',
            response.data
        )


        overview.value =
            response.data || {}


    }
    catch (error) {

        console.error(
            'Business Overview Error:',
            error
        )

        overview.value = {}

    }
    finally {

        loading.value = false

    }

}


/*
|--------------------------------------------------------------------------
| Watch Parent Filter
|--------------------------------------------------------------------------
|
| Whenever ReportsManagementView changes appliedFilters,
| Business Overview will reload automatically.
|
*/

watch(

    () => [
        props.filters?.date_from,
        props.filters?.date_to,
    ],

    ([newFrom, newTo]) => {

        console.log(
            'BUSINESS OVERVIEW FILTER CHANGED:',
            {
                date_from: newFrom,
                date_to: newTo,
            }
        )


        loadOverview({

            date_from: newFrom,
            date_to: newTo,

        })

    },

    {
        immediate: true,
    }

)


/*
|--------------------------------------------------------------------------
| Money Formatter
|--------------------------------------------------------------------------
*/

function money(value) {

    return (

        '৳ ' +

        Number(value || 0)
            .toLocaleString(
                'en-BD',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }
            )

    )

}

</script>

<style scoped>

.card {
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}
.hello{
    font-size:12px;
}

</style>